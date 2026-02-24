import { GoogleGenerativeAIEmbeddings } from "@langchain/google-genai";
import { TaskType } from "@google/generative-ai";
import dotenv from "dotenv";

dotenv.config();

export class EmbeddingsService {
    private embeddings: GoogleGenerativeAIEmbeddings;

    constructor() {
        if (!process.env.GOOGLE_API_KEY) {
            throw new Error("GOOGLE_API_KEY is not set in environment variables");
        }

        this.embeddings = new GoogleGenerativeAIEmbeddings({
            apiKey: process.env.GOOGLE_API_KEY,
            modelName: "gemini-embedding-001", // Reverted to available model
            taskType: TaskType.RETRIEVAL_DOCUMENT,
            title: "Product Embedding",
        });
    }

    /**
     * Generates a flattened string representation of a product for embedding.
     */
    public formatProductForEmbedding(product: any): string {
        const categories = [
            product.category_name,
            product.parent_category_name,
            product.grandparent_category_name
        ].filter(Boolean).join(" > ");

        const parts = [
            `Name: ${product.name}`,
            `CategoryPath: ${categories || "Uncategorized"}`,
            `Description: ${product.description || ""}`,
            `Brand: ${product.brand_name || ""}`,
        ];

        if (product.variations && product.variations.length > 0) {
            const variations = product.variations.map((v: any) => v.value).join(", ");
            parts.push(`Attributes: ${variations}`);
        }

        return parts.filter(p => !p.endsWith(": ")).join(" | ");
    }

    /**
     * Generates embeddings for a single text.
     */
    public async generateEmbedding(text: string): Promise<number[]> {
        return await this.embeddings.embedQuery(text);
    }

    /**
     * Generates embeddings for multiple texts in batch.
     */
    public async generateBatchEmbeddings(texts: string[]): Promise<number[][]> {
        return await this.embeddings.embedDocuments(texts);
    }
}

export const embeddingsService = new EmbeddingsService();
