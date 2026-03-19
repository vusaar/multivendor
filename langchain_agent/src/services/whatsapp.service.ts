import dotenv from 'dotenv';

dotenv.config();

const WHATSAPP_API_VERSION = 'v25.0'; // Or whichever version is current
const WHATSAPP_TOKEN = process.env.WHATSAPP_TOKEN;
const PHONE_NUMBER_ID = process.env.WHATSAPP_PHONE_NUMBER_ID;

export class WhatsAppService {
    async sendMessage(to: string, text: string) {
        if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
            console.error('WhatsApp credentials missing in .env');
            return;
        }

        const url = `https://graph.facebook.com/${WHATSAPP_API_VERSION}/${PHONE_NUMBER_ID}/messages`;
        console.log(`[WHATSAPP] Calling Meta API: ${url}`);

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
            console.log(`[WHATSAPP] Meta API Response:`, JSON.stringify(data, null, 2));

            if (!response.ok) {
                console.error('WhatsApp API Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error sending WhatsApp message:', error);
        }
    }

    async uploadMedia(imageUrl: string) {
        if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
            console.error('[WHATSAPP] Credentials missing in .env');
            return null;
        }

        console.log(`[WHATSAPP] Starting media upload from: ${imageUrl}`);

        try {
            // 1. Download the image
            const response = await fetch(imageUrl);
            if (!response.ok) {
                console.error(`[WHATSAPP] Failed to download image from ${imageUrl}: ${response.status} ${response.statusText}`);
                return null;
            }

            const blob = await response.blob();
            console.log(`[WHATSAPP] Downloaded image: size=${blob.size}, type=${blob.type}`);

            if (blob.size === 0) {
                console.error(`[WHATSAPP] Downloaded image is empty`);
                return null;
            }

            // 2. Prepare FormData
            const formData = new FormData();

            // Meta expects a file field with a filename
            // We'll use the blob and a generic name
            const extension = blob.type.split('/')[1] || 'jpg';
            const filename = `image.${extension}`;

            // Use append with filename to ensure Meta recognizes it as a file
            formData.append('file', blob, filename);
            formData.append('messaging_product', 'whatsapp');
            formData.append('type', blob.type);

            // 3. Upload to Meta
            const uploadUrl = `https://graph.facebook.com/${WHATSAPP_API_VERSION}/${PHONE_NUMBER_ID}/media`;
            const uploadRes = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                },
                body: formData
            });

            const data = await uploadRes.json();
            console.log(`[WHATSAPP] Meta Upload Response for ${imageUrl}:`, JSON.stringify(data));

            if (!uploadRes.ok) {
                console.error(`[WHATSAPP] Meta Media Upload Error for ${imageUrl}:`, JSON.stringify(data));
                return null;
            }

            console.log(`[WHATSAPP] Successfully uploaded media for ${imageUrl}. Media ID: ${data.id}`);
            return data.id;
        } catch (error: any) {
            console.error('[WHATSAPP] Error in uploadMedia:', error.message);
            return null;
        }
    }

    async sendImageById(to: string, mediaId: string, caption: string) {
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
                        id: mediaId,
                        caption: caption,
                    },
                }),
            });

            const data = await response.json();
            console.log(`[WHATSAPP] Meta SendByID Response:`, JSON.stringify(data, null, 2));

            if (!response.ok) {
                console.error('WhatsApp SendByID Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error in sendImageById:', error);
        }
    }

    async sendButtons(to: string, bodyText: string, buttons: { id: string, title: string }[]) {
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
                    type: 'interactive',
                    interactive: {
                        type: 'button',
                        body: {
                            text: bodyText,
                        },
                        action: {
                            buttons: buttons.map(button => ({
                                type: 'reply',
                                reply: {
                                    id: button.id,
                                    title: button.title,
                                },
                            })),
                        },
                    },
                }),
            });

            const data = await response.json();
            console.log(`[WHATSAPP] Meta Buttons Response:`, JSON.stringify(data, null, 2));

            if (!response.ok) {
                console.error('WhatsApp Buttons Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error in sendButtons:', error);
        }
    }

    async sendImageMessage(to: string, imageUrl: string, caption: string) {
        if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
            console.error('WhatsApp credentials missing in .env');
            return;
        }

        const url = `https://graph.facebook.com/${WHATSAPP_API_VERSION}/${PHONE_NUMBER_ID}/messages`;
        console.log(`[WHATSAPP] Calling Meta Image API: ${url}`);

        const payload = {
            messaging_product: 'whatsapp',
            recipient_type: 'individual',
            to: to,
            type: 'image',
            image: {
                link: imageUrl,
                caption: caption,
            },
        };
        console.log(`[WHATSAPP] Meta Image Payload:`, JSON.stringify(payload, null, 2));

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            console.log(`[WHATSAPP] Meta Image API Response:`, JSON.stringify(data, null, 2));

            if (!response.ok) {
                console.error('WhatsApp Image API Error:', data);
            }
            return data;
        } catch (error) {
            console.error('Error sending WhatsApp image message:', error);
        }
    }

    formatProductCaption(product: any): string {
        const name = `*${product.name.trim()}*`;
        const price = `💰 *Price:* ${product.price}`;

        // Ensure similarity_score is treated as a number
        const scoreVal = Number(product.similarity_score);
        const score = `🔍 *Search Score:* ${!isNaN(scoreVal) ? scoreVal.toFixed(4) : 'N/A'}`;

        // Extract unique properties from variations
        const properties: string[] = [];
        if (product.variations && product.variations.length > 0) {
            const allAttrValues = new Set<string>();
            product.variations.forEach((v: any) => {
                v.attribute_values.forEach((av: any) => {
                    allAttrValues.add(av.value);
                });
            });
            if (allAttrValues.size > 0) {
                properties.push(`📦 *Properties:* ${Array.from(allAttrValues).join(', ')}`);
            }
        }

        const description = product.description
            ? `\n\n📝 _${product.description.trim().substring(0, 200)}${product.description.length > 200 ? '...' : ''}_`
            : '';

        const propertiesText = properties.length > 0 ? `\n${properties.join('\n')}` : '';

        return `${name}\n${price}\n${score}${propertiesText}${description}`;
    }
}

export const whatsappService = new WhatsAppService();
