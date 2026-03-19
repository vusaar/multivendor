import dotenv from 'dotenv';

dotenv.config();

export interface SearchResult {
    data: any[];
    meta: any;
}

export class ProductSearchService {
    private readonly apiUrl: string;

    constructor(apiUrl?: string) {
        this.apiUrl = apiUrl || process.env.LARAVEL_API_URL || 'http://localhost/multistore/api/storefront/products/search';
    }

    async search(query: string, page: number = 1, userId?: string): Promise<SearchResult> {
        console.log(`[SEARCH SERVICE] Searching for: "${query}" (Page ${page})`);

        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                product: query,
                page: page,
                userId: userId
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error(`[SEARCH SERVICE] API Error: ${response.status}`, errorText);
            throw new Error(`Search API responded with status: ${response.status}`);
        }

        const data = await response.json();
        return {
            data: data.data || [],
            meta: data.meta || data
        };
    }
}

export const productSearchService = new ProductSearchService();
