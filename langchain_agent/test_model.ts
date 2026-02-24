import { model } from './src/config/llm';
import { HumanMessage } from "@langchain/core/messages";

async function testModel() {
    console.log("Testing model connection...");
    try {
        const response = await model.invoke([
            new HumanMessage("Hello, are you there?")
        ]);
        console.log("Model response:", response.content);
    } catch (error: any) {
        console.error("Model Error:", error.message);
    }
}

testModel().then(() => process.exit(0));
