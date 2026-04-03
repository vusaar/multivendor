import { whatsappService } from '../whatsapp.service';

describe('WhatsAppService Formatting', () => {
    const mockProduct = {
        name: 'Cool Sneakers',
        price: '50.00',
        description: 'Very cool shoes.',
        rrf_score: 195.4234,
        vendor_phone: '263777111222',
        vendor: { shop_name: 'Shoe Palace' },
        image: 'https://store.eyamisolutions.co.zw/storage/shoes.jpg'
    };

    it('should include the Match Relevance score in the caption, rounded to 2 decimal places', () => {
        const caption = whatsappService.formatProductCaption(mockProduct);
        
        expect(caption).toContain('*COOL SNEAKERS*');
        expect(caption).toContain('🔍 *Match Relevance:* 195.42');
        expect(caption).toContain('💰 *Price:* 50.00 USD');
    });

    it('should handle missing scores gracefully', () => {
        const productNoScore = { ...mockProduct, rrf_score: undefined };
        const caption = whatsappService.formatProductCaption(productNoScore);
        
        expect(caption).toContain('🔍 *Match Relevance:* N/A');
    });

    it('should handle other score field names (similarity_score, score)', () => {
        const productSim = { ...mockProduct, rrf_score: undefined, similarity_score: 85.1 };
        const caption = whatsappService.formatProductCaption(productSim);
        expect(caption).toContain('🔍 *Match Relevance:* 85.10');

        const productPlain = { ...mockProduct, rrf_score: undefined, score: 42 };
        const captionPlain = whatsappService.formatProductCaption(productPlain);
        expect(captionPlain).toContain('🔍 *Match Relevance:* 42.00');
    });

    it('should include vendor and chat links if available', () => {
        const caption = whatsappService.formatProductCaption(mockProduct);
        
        expect(caption).toContain('🏪 *Shop:* Shoe Palace');
        expect(caption).toContain('📱 *Chat:* https://wa.me/263777111222');
    });
});
