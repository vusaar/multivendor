"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.model = void 0;
const google_genai_1 = require("@langchain/google-genai");
const dotenv_1 = __importDefault(require("dotenv"));
dotenv_1.default.config();
if (!process.env.GOOGLE_API_KEY) {
    console.warn("WARNING: GOOGLE_API_KEY is not set in .env");
}
exports.model = new google_genai_1.ChatGoogleGenerativeAI({
    model: "gemini-2.0-flash", // Upgraded to full flash for Tier 1
    temperature: 0,
    maxOutputTokens: 1024,
    apiKey: process.env.GOOGLE_API_KEY,
    maxRetries: 0, // CRITICAL: Ensure zero delay during 429 rate limits
});
