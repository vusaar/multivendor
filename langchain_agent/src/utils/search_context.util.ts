export interface SearchPlan {
    originalQuery: string;
    parsedIntent: {
        query: string;
        categories?: string[];
        entity?: string;
        attributes?: string[];
        min_price?: number;
        max_price?: number;
    };
    embedding: number[]; // Stashed 3072-dim vector for gemini-embedding-001
    pagination: { offset: number; limit: number; };
    timestamp: number;
    results?: { id: string; name: string; score: number }[];
}

/**
 * Checks if a query is a continuation request (e.g., "more", "next", "next page").
 */
export function isContinuationQuery(query: string): boolean {
    const continuationPatterns = [
        /^\s*more\s*$/i,
        /^\s*next\s*$/i,
        /^\s*next\s*page\s*$/i,
        /^\s*show\s*more\s*$/i,
        /^\s*any\s*else\s*$/i,
        /^\s*keep\s*going\s*$/i
    ];
    return continuationPatterns.some(pattern => pattern.test(query));
}
