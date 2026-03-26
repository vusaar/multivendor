import { db } from '../config/database';
import dotenv from 'dotenv';

dotenv.config();

export interface SearchIntent {
    query?: string;
    entity?: string;
    categories?: string[];
    attributes?: string[];
    brand?: string;
}

export class SearchLoggerService {
    private enabled: boolean;

    constructor() {
        this.enabled = process.env.ENABLE_SEARCH_LOGGING === 'true';
    }

    /**
     * Log a search attempt to the database.
     * This is a "fire and forget" operation to minimize latency for the user.
     */
    async log(
        phoneNumber: string,
        query: string,
        correctedQuery: string | undefined,
        intent: SearchIntent,
        results: any,
        durationMs: number,
        searchId?: string
    ) {
        if (!this.enabled) return;

        try {
            const isArray = Array.isArray(results);
            const resultsCount = isArray ? results.length : 0;
            
            // Store only top 5 results to save space (only if array)
            let topResults: any[] = [];
            if (isArray) {
                topResults = results.slice(0, 5).map((p: any) => ({
                    id: p.id,
                    name: p.name,
                    score: p.similarity_score || p.rrf_score || p.final_score || 0
                }));
            }

            const sql = `
                INSERT INTO search_logs (
                    phone_number, 
                    query, 
                    corrected_query,
                    intent, 
                    results, 
                    results_count, 
                    duration_ms, 
                    search_id,
                    created_at, 
                    updated_at
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, NOW(), NOW())
            `;

            await db.query(sql, [
                phoneNumber,
                query,
                correctedQuery || query,
                JSON.stringify(intent),
                JSON.stringify(topResults),
                resultsCount,
                durationMs,
                searchId || null
            ]);

            console.log(`[LOGGER] Search logged: "${query}" -> "${correctedQuery || query}" for ${phoneNumber} (${resultsCount} results, ${durationMs}ms)`);
        } catch (error: any) {
            console.error('[LOGGER] Error logging search:', error.message);
        }
    }
}

export const searchLoggerService = new SearchLoggerService();
