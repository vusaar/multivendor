import { db } from "../src/config/database";
import { model } from "../src/config/llm";
import { embeddingsService } from "../src/services/embeddings.service";
import dotenv from "dotenv";

dotenv.config();

/**
 * Script to enrich categories with synonyms using Gemini AI.
 * This script:
 * 1. Fetches all categories from the DB.
 * 2. Asks Gemini to generate 5-7 general synonyms (English).
 * 3. Saves synonyms to categories table.
 * 4. Regenerates category embeddings using the new name + synonyms string.
 */
async function enrichCategories() {
    console.log("🚀 Starting Category Enrichment...");

    try {
        // 1. Fetch Categories
        const { rows: categories } = await db.query("SELECT id, name, slug FROM categories");
        console.log(`📊 Found ${categories.length} categories to enrich.`);

        for (const cat of categories) {
            console.log(`\n🔍 Processing: "${cat.name}" (ID: ${cat.id})`);

            // 2. Generate Synonyms
            const prompt = `Generate 5-7 high-quality, general synonyms or closely related search terms for the product category: "${cat.name}". 
            Respond ONLY with a JSON array of strings. 
            Example for "Men T-Shirts": ["tees", "jersey", "top", "cotton shirt", "golf shirt"]
            
            Focus on common global terms a customer might search for.`;

            const response = await model.invoke(prompt);
            const responseText = response.content as string;
            
            let synonyms: string[] = [];
            try {
                // Clean the response from markdown if present
                const cleanJson = responseText.replace(/```json|```/g, "").trim();
                synonyms = JSON.parse(cleanJson);
                console.log(`✅ Synonyms: ${synonyms.join(", ")}`);
            } catch (e) {
                console.error(`❌ Failed to parse synonyms for ${cat.name}:`, responseText);
                continue;
            }

            // 3. Generate New Embedding (Name + Synonyms)
            const enrichmentText = `${cat.name} | ${synonyms.join(", ")}`;
            const embedding = await embeddingsService.generateEmbedding(enrichmentText);
            const embeddingString = `[${embedding.join(",")}]`;

            // 4. Save to DB
            await db.query(
                "UPDATE categories SET synonyms = $1, embedding = $2::vector WHERE id = $3",
                [JSON.stringify(synonyms), embeddingString, cat.id]
            );

            console.log(`💾 Saved synonyms and embedding for ${cat.name}`);
        }

        console.log("\n✨ Category Enrichment Complete!");
        
        // 5. Trigger Re-indexing Message
        console.log("📢 IMPORTANT: Please run 'php artisan products:reindex' or trigger GenerateProductEmbedding for all items to update product search contexts.");

    } catch (error: any) {
        console.error("💥 Enrichment Error:", error.message);
    } finally {
        process.exit(0);
    }
}

enrichCategories();
