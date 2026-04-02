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
        const products = await processUserQuery(query, userId);

        // Map to the expected SearchResult format
        // The Agent already handles pagination/filtering internally
        return {
            data: products,
            meta: {
                current_page: page,
                last_page: 1, // Agent handles stateful pagination internally
                total: products.length
            }
        };
    }
}

export const productSearchService = new ProductSearchService();
