import { Request, Response } from 'express';
import { messageProcessorService } from '../services/message.processor.service';

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
                const entry = body.entry?.[0];
                const change = entry?.changes?.[0];
                const value = change?.value;
                const message = value?.messages?.[0];

                if (message) {
                    const from = message.from;
                    const msgBody = message.text?.body || null;
                    const interactive = message.interactive || null;

                    // Acknowledge receipt immediately to Meta to prevent retries
                    res.status(200).send('EVENT_RECEIVED');

                    // Process asynchronously via service
                    // Note: We don't await here to keep the webhook fast
                    messageProcessorService.handleIncomingMessage(from, msgBody, interactive)
                        .catch(err => console.error('[WHATSAPP] Async Error:', err));

                    return;
                }

                // If no message but valid structure, still acknowledge
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
