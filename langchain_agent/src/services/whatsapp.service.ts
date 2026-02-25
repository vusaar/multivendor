import dotenv from 'dotenv';

dotenv.config();

const WHATSAPP_API_VERSION = 'v17.0'; // Or whichever version is current
const WHATSAPP_TOKEN = process.env.WHATSAPP_TOKEN;
const PHONE_NUMBER_ID = process.env.WHATSAPP_PHONE_NUMBER_ID;

export class WhatsAppService {
    async sendMessage(to: string, text: string) {
        if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
            console.error('WhatsApp credentials missing in .env');
            return;
        }

        const url = `https://graph.facebook.com/${WHATSAPP_API_VERSION}/${PHONE_NUMBER_ID}/messages`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    messaging_product: 'whatsapp',
                    recipient_type: 'individual',
                    to: to,
                    type: 'text',
                    text: {
                        preview_url: false,
                        body: text,
                    },
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                console.error('WhatsApp API Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error sending WhatsApp message:', error);
        }
    }

    async sendImageMessage(to: string, imageUrl: string, caption: string) {
        if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
            console.error('WhatsApp credentials missing in .env');
            return;
        }

        const url = `https://graph.facebook.com/${WHATSAPP_API_VERSION}/${PHONE_NUMBER_ID}/messages`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    messaging_product: 'whatsapp',
                    recipient_type: 'individual',
                    to: to,
                    type: 'image',
                    image: {
                        link: imageUrl,
                        caption: caption,
                    },
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                console.error('WhatsApp Image API Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error sending WhatsApp image message:', error);
        }
    }

    formatProductCaption(product: any): string {
        return `*${product.name}*\nPrice: ${product.price}\n${product.description ? product.description.substring(0, 100) + '...' : ''}`;
    }
}

export const whatsappService = new WhatsAppService();
