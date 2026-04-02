import { db } from "../config/database";

export interface CategoryGuide {
    id: number;
    name: string;
    slug: string;
}

class CategoryGuideService {
    private cache: CategoryGuide[] | null = null;
    private lastFetch: number = 0;
    private readonly TTL = 10 * 60 * 1000; // 10 minutes

    async getCategoryMenu(): Promise<CategoryGuide[]> {
        const now = Date.now();
        if (this.cache && (now - this.lastFetch < this.TTL)) {
            return this.cache;
        }

        console.log("[CategoryGuide] Fetching fresh taxonomy guide from database...");
        const results = await db.query(
            "SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name ASC"
        );

        this.cache = results.rows.map(row => ({
            id: row.id,
            name: row.name,
            slug: row.slug
        }));
        this.lastFetch = now;

        return this.cache;
    }

    async getPromptSnippet(): Promise<string> {
        const menu = await this.getCategoryMenu();
        const guides = menu.map(c => `- ${c.slug} (${c.name})`).join("\n");
        
        return `### AVAILABLE CATEGORY SLUGS (MANDATORY SELECTION)
Select the SINGLE most relevant slug if the user intent matches a category. If no direct match, pick the most relevant parent.
${guides}`;
    }
}

export const categoryGuideService = new CategoryGuideService();
