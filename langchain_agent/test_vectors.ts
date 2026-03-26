import { embeddingsService } from './src/services/embeddings.service';

function cosineSimilarity(A: number[], B: number[]) {
    let dotProduct = 0;
    for (let i = 0; i < A.length; i++) {
        dotProduct += A[i] * B[i];
    }
    return dotProduct; // Gemini embeddings are pre-normalized, so dot product is exact cosine similarity
}

async function run() {
    try {
        console.log("Generating vectors...");
        
        const qVector = await embeddingsService.generateEmbedding("mens tshirts");
        
        const prodAMen = await embeddingsService.generateEmbedding("Name: T-shirt | CategoryPath: Men Apparel");
        const prodBGentlemen = await embeddingsService.generateEmbedding("Name: T-shirt | CategoryPath: Gentlemen's Apparel");
        const prodCLadies = await embeddingsService.generateEmbedding("Name: T-shirt | CategoryPath: Ladies Apparel");

        console.log("\n--- VECTOR SIMILARITY SCORES ---");
        console.log("Query: 'mens tshirts'");
        console.log(`1. vs 'Men Apparel T-shirt':        ${cosineSimilarity(qVector, prodAMen).toFixed(4)}`);
        console.log(`2. vs 'Gentlemen's Apparel T-shirt': ${cosineSimilarity(qVector, prodBGentlemen).toFixed(4)}`);
        console.log(`3. vs 'Ladies Apparel T-shirt':      ${cosineSimilarity(qVector, prodCLadies).toFixed(4)}`);
        
    } catch (e: any) {
        console.error(e.message);
    }
    process.exit(0);
}

run();
