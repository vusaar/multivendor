import { GoogleGenerativeAI } from "@google/generative-ai";
import dotenv from "dotenv";

dotenv.config();

async function listModels() {
    try {
        const genAI = new GoogleGenerativeAI(process.env.GOOGLE_API_KEY!);
        // The listModels method is on the genAI instance in newer versions, 
        // but let's try a different approach if it's missing.
        console.log("Fetching models...");
        const res = await fetch(`https://generativelanguage.googleapis.com/v1beta/models?key=${process.env.GOOGLE_API_KEY}`);
        const data = await res.json();

        if (data.models) {
            console.log("Available Models:");
            data.models.forEach((m: any) => {
                if (m.supportedGenerationMethods.includes("embedContent")) {
                    console.log(`- ${m.name} (Supported: ${m.supportedGenerationMethods.join(", ")})`);
                }
            });
        } else {
            console.log("No models returned. Response:", JSON.stringify(data));
        }
        process.exit(0);
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
}

listModels();
