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
        intent: SearchIntent,
        results: any[],
        durationMs: number
    ) {
        if (!this.enabled) return;

        try {
            const resultsCount = results.length;
            // Store only top 5 results to save space
            const topResults = results.slice(0, 5).map(p => ({
                id: p.id,
                name: p.name,
                score: p.similarity_score || p.rrf_score || 0
            }));

            const sql = `
                INSERT INTO search_logs (
                    phone_number, 
                    query, 
                    intent, 
                    results, 
                    results_count, 
                    duration_ms, 
                    created_at, 
                    updated_at
                ) VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW())
            `;

            await db.query(sql, [
                phoneNumber,
                query,
                JSON.stringify(intent),
                JSON.stringify(topResults),
                resultsCount,
                durationMs
            ]);

            console.log(`[LOGGER] Search logged: "${query}" for ${phoneNumber} (${resultsCount} results, ${durationMs}ms)`);
        } catch (error: any) {
            console.error('[LOGGER] Error logging search:', error.message);
        }
    }
}

export const searchLoggerService = new SearchLoggerService();
