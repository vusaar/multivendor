import { ProductSearchService } from '../product.search.service';

describe('ProductSearchService', () => {
    let service: ProductSearchService;
    const mockApiUrl = 'http://test-api.com';

    beforeEach(() => {
        service = new ProductSearchService(mockApiUrl);
        // Reset global fetch mock
        (global as any).fetch = jest.fn();
    });

    it('should successfully search products', async () => {
        const mockData = {
            data: [{ id: 1, name: 'Test Product' }],
            meta: { current_page: 1, last_page: 2 }
        };

        (global as any).fetch.mockResolvedValue({
            ok: true,
            json: async () => mockData
        });

        const result = await service.search('test query', 1, 'user123');

        expect(global.fetch).toHaveBeenCalledWith(mockApiUrl, expect.objectContaining({
            method: 'POST',
            body: JSON.stringify({
                product: 'test query',
                page: 1,
                userId: 'user123'
            })
        }));
        expect(result.data).toEqual(mockData.data);
        expect(result.meta).toEqual(mockData.meta);
    });

    it('should throw an error if API responds with non-ok status', async () => {
        (global as any).fetch.mockResolvedValue({
            ok: false,
            status: 500,
            text: async () => 'Internal Server Error'
        });

        await expect(service.search('query')).rejects.toThrow('Search API responded with status: 500');
    });
});
