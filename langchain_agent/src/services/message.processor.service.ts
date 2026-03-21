import { whatsappService } from './whatsapp.service';
import { productSearchService } from './product.search.service';
import { sessionService } from './session.service';

export class MessageProcessorService {
    /**
     * Main entry point for processing a WhatsApp message or button reply
     */
    async handleIncomingMessage(from: string, msgBody: string | null, interactive: any | null) {
        try {
            const isButtonReply = interactive?.type === 'button_reply';
            const buttonId = isButtonReply ? interactive.button_reply.id : null;

            console.log(`[MESSAGE PROCESSOR] Handling ${isButtonReply ? 'button' : 'message'} from ${from}: ${buttonId || msgBody}`);

            // 1. Get/Sync session
            let session = await sessionService.getSession(from);

            let queryText = msgBody;
            let page = 1;
            let productsToShow: any[] = [];
            let suggestedProducts: any[] = [];

            // 2. Handle button actions or new search
            if (isButtonReply && buttonId === 'next_page') {
                queryText = session.lastQuery;
                page = (session.currentPage || 1) + 1;
                console.log(`[MESSAGE PROCESSOR] Pagination for ${from}. Page ${page} for "${queryText}"`);
            } else if (isButtonReply && buttonId === 'show_suggestions') {
                // Return suggested products from session
                const suggestions = session.suggestedProducts || [];
                console.log(`[MESSAGE PROCESSOR] Showing ${suggestions.length} suggestions to ${from}`);
                
                await whatsappService.sendMessage(from, "Here are some other items you might find interesting:");
                for (const product of suggestions) {
                    await this.sendProductMessage(from, product);
                }
                // Clear suggestions after showing them to keep session clean
                await sessionService.updateSession(from, { suggestedProducts: [] });
                return;
            } else {
                // New search, update lastQuery in session
                await sessionService.updateSession(from, { lastQuery: msgBody, currentPage: 1, suggestedProducts: [] });
            }

            if (!queryText) return;

            // 3. Search Products
            const searchData = await productSearchService.search(queryText, page, from);
            const rawProducts = searchData.data || [];
            const meta = searchData.meta;

            const currentPage = meta.current_page || page;
            const lastPage = meta.last_page || 1;

            // 4. Partition products by confidence score (0.025)
            const THRESHOLD = 0.025;
            const verifiedProducts = rawProducts.filter((p: any) => (p.similarity_score || 0) >= THRESHOLD);
            const potentialMatches = rawProducts.filter((p: any) => (p.similarity_score || 0) < THRESHOLD);

            console.log(`[MESSAGE PROCESSOR] Found ${verifiedProducts.length} verified and ${potentialMatches.length} suggested products.`);

            // 5. Send Responses
            if (verifiedProducts.length === 0 && currentPage === 1) {
                if (potentialMatches.length > 0) {
                    // Only suggestions found
                    await sessionService.updateSession(from, { suggestedProducts: potentialMatches });
                    await whatsappService.sendButtons(from, 
                        `I couldn't find an exact match for "${queryText}". Would you like to see some items that are similar?`, 
                        [{ id: 'show_suggestions', title: 'Show Suggestions 🔍' }]
                    );
                } else {
                    await whatsappService.sendMessage(from, `Sorry, I couldn't find any products matching "${queryText}".`);
                }
            } else {
                // Show verified products
                for (const product of verifiedProducts) {
                    await this.sendProductMessage(from, product);
                }

                // Update session state
                await sessionService.updateSession(from, { 
                    currentPage: currentPage,
                    suggestedProducts: potentialMatches 
                });

                // Show action buttons
                const buttons = [];
                if (currentPage < lastPage) {
                    buttons.push({ id: 'next_page', title: 'Next ➡️' });
                }
                if (potentialMatches.length > 0) {
                    buttons.push({ id: 'show_suggestions', title: 'Show More 🔍' });
                }

                if (buttons.length > 0) {
                    const label = (currentPage < lastPage) ? "Would you like to see more?" : "We found more potential matches:";
                    await whatsappService.sendButtons(from, label, buttons);
                }
            }
        } catch (error: any) {
            console.error('[MESSAGE PROCESSOR] Error:', error.message);
            await whatsappService.sendMessage(from, "Sorry, something went wrong while processing your request.");
        }
    }

    private async sendProductMessage(to: string, product: any) {
        const hasImages = product.images && product.images.length > 0;
        const imageUrl = hasImages
            ? product.images[0]
            : "http://127.0.0.1:8000/storage/placeholder.png";

        const caption = whatsappService.formatProductCaption(product);

        try {
            const mediaId = await whatsappService.uploadMedia(imageUrl);
            if (mediaId) {
                await whatsappService.sendImageById(to, mediaId, caption);
            } else {
                await whatsappService.sendMessage(to, caption);
            }
        } catch (err) {
            console.error(`[MESSAGE PROCESSOR] Failed to send product ${product.name}:`, err);
            await whatsappService.sendMessage(to, caption);
        }
    }
}

export const messageProcessorService = new MessageProcessorService();
