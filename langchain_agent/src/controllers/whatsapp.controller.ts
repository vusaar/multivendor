import { Request, Response } from 'express';
import { whatsappService } from '../services/whatsapp.service';
import { processUserQuery } from '../services/search.agent';
import { sessionService } from '../services/session.service';

export const whatsappController = {
    /**
     * Webhook verification for Meta
     */
    verifyWebhook: (req: Request, res: Response) => {
        const mode = req.query['hub.mode'];
        const token = req.query['hub.verify_token'];
        const challenge = req.query['hub.challenge'];

        const verifyToken = process.env.WHATSAPP_VERIFY_TOKEN;

        if (mode && token) {
            if (mode === 'subscribe' && token === verifyToken) {
                console.log('[WHATSAPP] Webhook verified');
                return res.status(200).send(challenge);
            } else {
                return res.status(403).send('Forbidden');
            }
        }
        return res.status(400).send('Bad Request');
    },

    /**
     * Handle incoming WhatsApp messages
     */
    handleWebhook: async (req: Request, res: Response) => {
        try {
            const body = req.body;

            // Check if it's a WhatsApp message notification
            if (body.object === 'whatsapp_business_account') {
                if (
                    body.entry &&
                    body.entry[0].changes &&
                    body.entry[0].changes[0].value.messages &&
                    body.entry[0].changes[0].value.messages[0]
                ) {
                    const message = body.entry[0].changes[0].value.messages[0];
                    const from = message.from; // User's phone number
                    const msgBody = message.text ? message.text.body : null;

                    if (msgBody) {
                        console.log(`[WHATSAPP] Received message from ${from}: ${msgBody}`);

                        // Sync session
                        await sessionService.getSession(from);

                        // Call Laravel Search API
                        const laravelUrl = process.env.LARAVEL_API_URL || 'http://localhost/multistore/api/storefront/products/search';

                        try {
                            const response = await fetch(laravelUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ product: msgBody })
                            });

                            const searchData = await response.json();
                            const products = searchData.data || [];

                            if (products.length === 0) {
                                await whatsappService.sendMessage(from, "Sorry, I couldn't find any products matching your search.");
                            } else {
                                // Loop through results and send each as a separate message
                                for (const product of products) {
                                    const imageUrl = product.images && product.images.length > 0 ? product.images[0] : null;
                                    const caption = whatsappService.formatProductCaption(product);

                                    if (imageUrl) {
                                        await whatsappService.sendImageMessage(from, imageUrl, caption);
                                    } else {
                                        await whatsappService.sendMessage(from, caption);
                                    }
                                }
                            }
                        } catch (apiError: any) {
                            console.error('[WHATSAPP] Laravel API Error:', apiError.message);
                            await whatsappService.sendMessage(from, "Sorry, I encountered an error while searching for products.");
                        }
                    }
                }
                return res.status(200).send('EVENT_RECEIVED');
            } else {
                return res.status(404).send();
            }
        } catch (error: any) {
            console.error('[WHATSAPP] Webhook error:', error);
            return res.status(500).send();
        }
    }
};
