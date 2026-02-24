import { ChatGoogleGenerativeAI } from "@langchain/google-genai";
import dotenv from 'dotenv';

dotenv.config();

if (!process.env.GOOGLE_API_KEY) {
    console.warn("WARNING: GOOGLE_API_KEY is not set in .env");
}

export const model = new ChatGoogleGenerativeAI({
    model: "gemini-2.0-flash-lite", // Re-activated identified model
    temperature: 0,
    maxOutputTokens: 1024,
    apiKey: process.env.GOOGLE_API_KEY,
    maxRetries: 0, // CRITICAL: Ensure zero delay during 429 rate limits
});
