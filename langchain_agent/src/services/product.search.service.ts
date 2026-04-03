import { processUserQuery } from './search.agent';

export interface SearchResult {
    data: any[];
    meta: any;
}

export class ProductSearchService {
    constructor() {}

    async search(query: string, page: number = 1, userId: string = 'default'): Promise<SearchResult> {
        console.log(`[SEARCH SERVICE] Routing core search to AI Agent: "${query}" (Page ${page})`);

        // Use the AI Agent (which uses vector_search.tool.ts + LLM intent)
        const response = await processUserQuery(query, userId, page);
        const products = response.products;
        const total = response.total;

        // Map to the expected SearchResult format
        // The Agent already handles pagination/filtering internally
        return {
            data: products,
            meta: {
                current_page: page,
                last_page: Math.ceil(total / 3), // Using the new LIMIT_VERIFIED=3
                total: total
            }
        };
    }
}

export const productSearchService = new ProductSearchService();
