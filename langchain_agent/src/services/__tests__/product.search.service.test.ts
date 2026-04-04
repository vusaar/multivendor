import { productSearchService } from '../product.search.service';
import { processUserQuery } from '../search.agent';

// Mock the search agent
jest.mock('../search.agent', () => ({
    processUserQuery: jest.fn()
}));

describe('ProductSearchService (AI Agent Mode)', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('should successfully search products using the AI Agent', async () => {
        const mockAgentResponse = {
            products: [{ id: 1, name: 'Agent Product', rrf_score: 195.5 }],
            total: 10
        };

        (processUserQuery as jest.Mock).mockResolvedValue(mockAgentResponse);

        const result = await productSearchService.search('blue shoes', 1, 'user123');

        expect(processUserQuery).toHaveBeenCalledWith('blue shoes', 'user123', 1);
        expect(result.data).toEqual(mockAgentResponse.products);
        expect(result.meta.total).toBe(10);
        expect(result.meta.current_page).toBe(1);
        expect(result.meta.last_page).toBe(4); // ceil(10 / 3)
    });

    it('should correctly calculate pagination for different total counts', async () => {
        (processUserQuery as jest.Mock).mockResolvedValue({
            products: [],
            total: 5
        });

        const result = await productSearchService.search('any', 1, 'test');
        expect(result.meta.last_page).toBe(2); // ceil(5 / 3)
    });
});
