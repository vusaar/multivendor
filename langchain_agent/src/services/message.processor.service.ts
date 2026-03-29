import { whatsappService } from './whatsapp.service';
import { productSearchService } from './product.search.service';
import { sessionService } from './session.service';
import { SEARCH_CONFIG } from '../config/search';

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
            let isDebug = msgBody?.toLowerCase().includes('debug') || false;

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
                    await this.sendProductMessage(from, product, isDebug);
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

            // GREETING/HELP FLOW: If the agent returned a direct message, send it and stop
            if (rawProducts.length > 0 && rawProducts[0].id === 'AI_MESSAGE') {
                const aiMsg = rawProducts[0].text || "How can I help you today?";
                console.log(`[MESSAGE PROCESSOR] Special message from agent: ${aiMsg}`);
                await whatsappService.sendMessage(from, aiMsg);
                return;
            }

            const meta = searchData.meta;

            const currentPage = meta.current_page || page;
            const lastPage = meta.last_page || 1;

            // 4. Send Responses
            const { THRESHOLD_VERIFIED } = SEARCH_CONFIG;
            // Support both internal agent (rrf_score) and Laravel API (similarity_score) formats
            const getScore = (p: any) => p.similarity_score !== undefined ? p.similarity_score : (p.rrf_score !== undefined ? p.rrf_score : (p.score || 0));
            
            const verifiedProducts = rawProducts.filter((p: any) => getScore(p) >= THRESHOLD_VERIFIED);
            const suggestedProducts = rawProducts.filter((p: any) => getScore(p) < THRESHOLD_VERIFIED);

            if (verifiedProducts.length === 0 && suggestedProducts.length === 0) {
                await whatsappService.sendMessage(from, `Sorry, I couldn't find any products matching "${queryText}".`);
                return;
            }

            // Show verified products first
            for (const product of verifiedProducts) {
                await this.sendProductMessage(from, product, isDebug);
            }

            // If we have suggestions, handle based on whether we have verified matches
            if (suggestedProducts.length > 0) {
                if (verifiedProducts.length > 0) {
                    await whatsappService.sendMessage(from, "✨ *YOU MIGHT ALSO LIKE* ✨");
                    for (const product of suggestedProducts) {
                        await this.sendProductMessage(from, product, isDebug);
                    }
                } else {
                    // INTERACTIVE FLOW: Ask permission if NO verified matches
                    console.log(`[MESSAGE PROCESSOR] Only suggestions for "${queryText}". Asking for permission.`);
                    await sessionService.updateSession(from, { suggestedProducts: suggestedProducts });
                    await whatsappService.sendButtons(
                        from, 
                        `I couldn't find any "${queryText}" in stock, but I found some other similar items. Would you like to see them?`, 
                        [{ id: 'show_suggestions', title: 'See similar items' }]
                    );
                    return; // Stop here and wait for button click
                }
            }

            // 5. Update session state
            await sessionService.updateSession(from, { 
                currentPage: currentPage,
                suggestedProducts: [] // We already showed them
            });

            // 6. Pagination UI: Send "View More" button if more pages exist
            if (currentPage < lastPage) {
                await whatsappService.sendButtons(
                    from, 
                    `Page ${currentPage} of ${lastPage}: Would you like to see more results?`, 
                    [{ id: 'next_page', title: 'View More Results' }]
                );
            }
        } catch (error: any) {
            console.error('[MESSAGE PROCESSOR] Error:', error.message);
            await whatsappService.sendMessage(from, "Sorry, something went wrong while processing your request.");
        }
    }

    private async sendProductMessage(to: string, product: any, debug: boolean = false) {
        const hasImages = product.images && product.images.length > 0;
        const imageUrl = hasImages
            ? product.images[0]
            : "http://127.0.0.1:8000/storage/placeholder.png";

        const caption = whatsappService.formatProductCaption(product, debug);

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
