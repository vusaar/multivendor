import { model } from "../config/llm";
import { HumanMessage } from "@langchain/core/messages";
import * as fs from 'fs';
import * as path from 'path';

const UNSPLASH_ACCESS_KEY = "UCJMYOIpwMApxtSQkxQprqW3mhSM5tiS6X592P-BSNI";

interface UnsplashPhoto {
    id: string;
    description: string | null;
    alt_description: string | null;
    urls: {
        regular: string;
    };
    tags?: { title: string }[];
}

interface PreparedProduct {
    external_id: string;
    name: string;
    brand: string;
    description: string;
    price: number;
    category_id: number;
    image_url: string;
    attributes: any;
    is_prominent: boolean;
}

async function fetchFromUnsplash(query: string, count: number = 10): Promise<UnsplashPhoto[]> {
    console.log(`[UNSPLASH] Searching for "${query}" (count: ${count})...`);
    const url = `https://api.unsplash.com/search/photos?query=${encodeURIComponent(query)}&per_page=${count}&client_id=${UNSPLASH_ACCESS_KEY}`;
    
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Unsplash API error: ${response.statusText}`);
        }
        const data = await response.json();
        return data.results;
    } catch (error) {
        console.error("[UNSPLASH] Fetch failed:", error);
        return [];
    }
}

async function enrichWithAI(query: string, photo: UnsplashPhoto, categoryId: number): Promise<PreparedProduct | null> {
    const tags = photo.tags?.map(t => t.title).join(", ") || "";
    const context = `
        Search Query: ${query}
        Unsplash Description: ${photo.description || "N/A"}
        Alt Description: ${photo.alt_description || "N/A"}
        Tags: ${tags}
    `;

    const prompt = `
        You are an e-commerce expert. Based on the following metadata from an Unsplash photo of a sneaker/shoe, generate a structured product entry.
        
        Context:
        ${context}

        Requirements:
        1. "name": Generate a catchy, realistic commercial name for this shoe (e.g., "Air Glide Pro 2").
        2. "brand": Identify or assign a realistic brand (e.g., "Nike", "Adidas", "Puma", or a plausible high-quality generic if unknown).
        3. "description": Write exactly ONE sentence of less than 10 words (e.g., "Lightweight mesh running shoes for maximum speed.")
        4. "price": Generate a random price between 20.00 and 60.00.
        5. "attributes": Create a JSON object of key properties like "color", "material", "use_case" (e.g., "running", "casual"), and "style".
        6. "is_prominent": Evaluate if this is a high-quality, prominent product photo suitable for an e-commerce catalog. Set to false if it is a busy lifestyle shot (e.g. person running in distance), if the product is too small, or if there is too much clutter. Set to true if it is a clear, centralized photo of the footwear.

        Output ONLY a valid JSON object.
    `;

    try {
        const response = await model.invoke([new HumanMessage(prompt)]);
        const content = typeof response.content === 'string' ? response.content : "";
        
        // Clean up JSON if it contains markdown blocks
        const jsonStr = content.replace(/```json/g, "").replace(/```/g, "").trim();
        const enriched = JSON.parse(jsonStr);

        return {
            external_id: photo.id,
            name: enriched.name,
            brand: enriched.brand,
            description: enriched.description,
            price: enriched.price,
            category_id: categoryId,
            image_url: photo.urls.regular.replace(/&?crop=[^&]+/g, ''),
            attributes: enriched.attributes,
            is_prominent: enriched.is_prominent ?? true
        };
    } catch (error) {
        console.error(`[AI] Enrichment failed for photo ${photo.id}:`, error);
        return null;
    }
}

async function main() {
    const args = process.argv.slice(2);
    const query = args[0] || "adidas running shoes";
    const count = parseInt(args[1]) || 5;
    const categoryId = parseInt(args[2]) || 107; // Default 107 (Men Sneakers)

    const photos = await fetchFromUnsplash(query, count);
    const products: PreparedProduct[] = [];

    console.log(`[AI] Processing ${photos.length} photos with LLM...`);
    
    for (const photo of photos) {
        const product: any = await enrichWithAI(query, photo, categoryId);
        if (product && product.is_prominent) {
            products.push(product);
            console.log(`[+] Enriched: ${product.name} (${product.brand}) - $${product.price}`);
        } else if (product) {
            console.log(`[-] Skipped: ${product.name} (Not prominent)`);
        }
    }

    const outputDir = path.join(__dirname, "../../../database/data");
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    const outputPath = path.join(outputDir, "prepared_products.json");
    
    // Load existing data if it exists
    let allProducts = [];
    if (fs.existsSync(outputPath)) {
        try {
            allProducts = JSON.parse(fs.readFileSync(outputPath, 'utf8'));
        } catch (e) {
            allProducts = [];
        }
    }

    // Append new products (avoiding duplicates by external_id)
    const existingIds = new Set(allProducts.map((p: any) => p.external_id));
    for (const p of products) {
        if (!existingIds.has(p.external_id)) {
            allProducts.push(p);
        }
    }

    fs.writeFileSync(outputPath, JSON.stringify(allProducts, null, 2));
    console.log(`\n[DONE] Saved ${products.length} new products to ${outputPath}`);
    console.log(`Total items in pool: ${allProducts.length}`);
}

main().catch(console.error);
