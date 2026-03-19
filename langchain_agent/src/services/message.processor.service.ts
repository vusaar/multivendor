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

            // 2. Determine query and page
            if (isButtonReply && buttonId === 'next_page') {
                queryText = session.lastQuery;
                page = (session.currentPage || 1) + 1;
                console.log(`[MESSAGE PROCESSOR] Pagination for ${from}. Page ${page} for "${queryText}"`);
            } else {
                // New search, update lastQuery in session
                await sessionService.updateSession(from, { lastQuery: msgBody, currentPage: 1 });
            }

            if (!queryText) return;

            // 3. Search Products
            const searchData = await productSearchService.search(queryText, page, from);
            const products = searchData.data || [];
            const meta = searchData.meta;

            const currentPage = meta.current_page || page;
            const lastPage = meta.last_page || 1;

            console.log(`[MESSAGE PROCESSOR] Found ${products.length} products for ${from}. Page ${currentPage}/${lastPage}`);

            // 4. Send Responses
            if (products.length === 0 && currentPage === 1) {
                await whatsappService.sendMessage(from, "Sorry, I couldn't find any products matching your search.");
            } else {
                for (const product of products) {
                    await this.sendProductMessage(from, product);
                }

                // Update session state
                await sessionService.updateSession(from, { currentPage: currentPage });

                // Show pagination if needed
                if (currentPage < lastPage) {
                    await whatsappService.sendButtons(from, "Would you like to see more products?", [
                        { id: 'next_page', title: 'Next ➡️' }
                    ]);
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
