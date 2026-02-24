"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.embeddingsService = exports.EmbeddingsService = void 0;
const google_genai_1 = require("@langchain/google-genai");
const generative_ai_1 = require("@google/generative-ai");
const dotenv_1 = __importDefault(require("dotenv"));
dotenv_1.default.config();
class EmbeddingsService {
    constructor() {
        if (!process.env.GOOGLE_API_KEY) {
            throw new Error("GOOGLE_API_KEY is not set in environment variables");
        }
        this.embeddings = new google_genai_1.GoogleGenerativeAIEmbeddings({
            apiKey: process.env.GOOGLE_API_KEY,
            modelName: "gemini-embedding-001", // Reverted to available model
            taskType: generative_ai_1.TaskType.RETRIEVAL_DOCUMENT,
            title: "Product Embedding",
        });
    }
    /**
     * Generates a flattened string representation of a product for embedding.
     */
    formatProductForEmbedding(product) {
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
            const variations = product.variations.map((v) => v.value).join(", ");
            parts.push(`Attributes: ${variations}`);
        }
        return parts.filter(p => !p.endsWith(": ")).join(" | ");
    }
    /**
     * Generates embeddings for a single text.
     */
    async generateEmbedding(text) {
        return await this.embeddings.embedQuery(text);
    }
    /**
     * Generates embeddings for multiple texts in batch.
     */
    async generateBatchEmbeddings(texts) {
        return await this.embeddings.embedDocuments(texts);
    }
}
exports.EmbeddingsService = EmbeddingsService;
exports.embeddingsService = new EmbeddingsService();
