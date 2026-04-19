import { db } from "../config/database";
import { embeddingsService } from "../services/embeddings.service";

async function vectorizePendingProducts() {
    console.log("[VECTORIZE] Starting vectorization for products with missing embeddings...");

    // 1. Fetch products that need vectorization
    // We join with brands and categories to get the full context for the embedding
    const query = `
        SELECT 
            p.id, 
            p.name, 
            p.description, 
            b.name as brand_name,
            c.name as category_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.embedding IS NULL
    `;

    try {
        const result = await db.query(query);
        const products = result.rows;

        if (products.length === 0) {
            console.log("[VECTORIZE] No products found with missing embeddings.");
            return;
        }

        console.log(`[VECTORIZE] Found ${products.length} products to process.`);

        for (const product of products) {
            try {
                // 2. Format context for embedding
                // Similar to the logic in vector_search.tool.ts
                const contextParts = [
                    `Name: ${product.name}`,
                    `Brand: ${product.brand_name || ""}`,
                    `Category: ${product.category_name || ""}`,
                    `Description: ${product.description || ""}`
                ];
                
                // Fetch variations for extra context
                const varQuery = `
                    SELECT v.value 
                    FROM variation_attribute_values v
                    JOIN product_variation_attribute_value pv ON v.id = pv.variation_attribute_value_id
                    JOIN product_variations pr ON pv.product_variation_id = pr.id
                    WHERE pr.product_id = $1
                `;
                const varResult = await db.query(varQuery, [product.id]);
                if (varResult.rows.length > 0) {
                    const attrs = varResult.rows.map(r => r.value).join(", ");
                    contextParts.push(`Attributes: ${attrs}`);
                }

                const searchContext = contextParts.filter(p => !p.endsWith(": ")).join(" | ");

                // 3. Generate Embedding
                console.log(`[VECTORIZE] Processing: ${product.name}...`);
                const embedding = await embeddingsService.generateEmbedding(searchContext);

                // 4. Update Database
                // Note: The embedding column is a vector(3072)
                const updateQuery = `
                    UPDATE products 
                    SET 
                        search_context = $1,
                        embedding = $2::vector
                    WHERE id = $3
                `;
                
                // Format vector for pgvector: [0.1, 0.2, ...]
                const vectorStr = `[${embedding.join(',')}]`;
                
                await db.query(updateQuery, [searchContext, vectorStr, product.id]);
                
            } catch (err) {
                console.error(`[VECTORIZE] Failed to process product ${product.id}:`, err);
            }
        }

        console.log("[VECTORIZE] All pending products have been vectorized.");
    } catch (error) {
        console.error("[VECTORIZE] Database error:", error);
    } finally {
        // Exit process
        setTimeout(() => process.exit(0), 1000);
    }
}

vectorizePendingProducts();
