import { processUserQuery } from '../search.agent';
import { model } from "../../config/llm";
import { executeHybridSearch, hybridSearchTool } from "../../tools/vector_search.tool";
import { sessionService } from "../session.service";
import { categoryGuideService } from "../category.guide.service";
import { embeddingsService } from "../embeddings.service";

// Mock everything
jest.mock('../../config/llm', () => ({
    model: {
        bindTools: jest.fn().mockReturnThis(),
        invoke: jest.fn(),
    }
}));
jest.mock('../../tools/vector_search.tool', () => ({
    executeHybridSearch: jest.fn(),
    hybridSearchTool: {
        id: "hybrid_product_search",
        name: "hybrid_product_search",
        invoke: jest.fn(),
        call: jest.fn(),
    }
}));
jest.mock('../session.service');
jest.mock('../category.guide.service');
jest.mock('../embeddings.service');
jest.mock('../logger.service');

describe('SearchAgent Intent Extraction', () => {
    const userId = 'test-user';

    beforeEach(() => {
        jest.clearAllMocks();
        (sessionService.getSession as jest.Mock).mockResolvedValue({});
        (categoryGuideService.getPromptSnippet as jest.Mock).mockResolvedValue("AVAILABLE CATEGORIES: men-tops, women-dresses");
        (embeddingsService.generateEmbedding as jest.Mock).mockResolvedValue(new Array(3072).fill(0.1));
    });

    it('should bypass tools and return a direct message for a greeting', async () => {
        const response = await processUserQuery('Hello!', userId);
        
        expect(response.products[0].id).toBe('AI_MESSAGE');
        expect(response.products[0].text).toContain("Hello! I'm your AI shopping assistant");
        expect(model.invoke).not.toHaveBeenCalled(); // Fast bypass doesn't call LLM
    });

    it('should call hybrid_product_search when a product is requested', async () => {
        const mockLLMResponse = {
            tool_calls: [{
                name: 'hybrid_product_search',
                args: { query: 'blue shoes', categories: ['men'] }
            }]
        };
        (model.invoke as jest.Mock).mockResolvedValue(mockLLMResponse);

        const mockProducts = [
            { id: 1, name: 'Blue Shoe', rrf_score: 150, total_count: 1 }
        ];
        (hybridSearchTool.invoke as jest.Mock).mockResolvedValue(JSON.stringify(mockProducts));

        const response = await processUserQuery('Find me some blue shoes for men', userId);

        expect(model.invoke).toHaveBeenCalled();
        expect(hybridSearchTool.invoke).toHaveBeenCalledWith(expect.objectContaining({
            query: 'blue shoes',
            categories: ['men']
        }));
        expect(response.products).toHaveLength(1);
        expect(response.total).toBe(1);
    });

    it('should handle demographics intent ("for her") correctly', async () => {
        const mockLLMResponse = {
            tool_calls: [{
                name: 'hybrid_product_search',
                args: { query: 'gift', categories: ['women'] }
            }]
        };
        (model.invoke as jest.Mock).mockResolvedValue(mockLLMResponse);
        (hybridSearchTool.invoke as jest.Mock).mockResolvedValue(JSON.stringify([]));

        await processUserQuery('something for her', userId);

        expect(hybridSearchTool.invoke).toHaveBeenCalledWith(expect.objectContaining({
            categories: ['women']
        }));
    });

    it('should use stateful bypass for repeat queries', async () => {
        const mockPlan = {
            originalQuery: 'red dress',
            parsedIntent: { query: 'red dress' },
            embedding: new Array(3072).fill(0.1),
            pagination: { offset: 0, limit: 3 }
        };
        (sessionService.getSession as jest.Mock).mockResolvedValue({ lastSearchPlan: mockPlan });
        (executeHybridSearch as jest.Mock).mockResolvedValue([
            { id: 10, name: 'Red Dress', rrf_score: 200, total_count: 5 }
        ]);

        const response = await processUserQuery('red dress', userId);

        expect(executeHybridSearch).toHaveBeenCalled();
        expect(model.invoke).not.toHaveBeenCalled();
        expect(response.products[0].name).toBe('Red Dress');
    });

    it('should correctly extract attributes (colors, materials) from the prompt', async () => {
        const mockLLMResponse = {
            tool_calls: [{
                name: 'hybrid_product_search',
                args: { query: 'cotton shirt', attributes: ['white', 'cotton'] }
            }]
        };
        (model.invoke as jest.Mock).mockResolvedValue(mockLLMResponse);
        (hybridSearchTool.invoke as jest.Mock).mockResolvedValue(JSON.stringify([]));

        await processUserQuery('I want a white cotton shirt', userId);

        expect(hybridSearchTool.invoke).toHaveBeenCalledWith(expect.objectContaining({
            attributes: ['white', 'cotton']
        }));
    });

    it('should correctly map complex taxonomy intents (jumper -> men-tops)', async () => {
        const mockLLMResponse = {
            tool_calls: [{
                name: 'hybrid_product_search',
                args: { 
                    query: 'jumper', 
                    target_category_slug: 'men-tops', 
                    synonyms: ['sweater', 'pullover'],
                    categories: ['men']
                }
            }]
        };
        (model.invoke as jest.Mock).mockResolvedValue(mockLLMResponse);
        (hybridSearchTool.invoke as jest.Mock).mockResolvedValue(JSON.stringify([]));

        await processUserQuery('jumper for him', userId);

        expect(hybridSearchTool.invoke).toHaveBeenCalledWith(expect.objectContaining({
            target_category_slug: 'men-tops',
            synonyms: ['sweater', 'pullover'],
            categories: ['men']
        }));
    });
});
