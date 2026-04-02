import { model } from "./src/config/llm";
import { hybridSearchTool } from "./src/tools/vector_search.tool";
import { HumanMessage } from "@langchain/core/messages";
require('dotenv').config();

const systemPrompt = `You are a helpful AI shopping assistant.
### 3. DATABASE MAPPING
- 'synonyms': ALWAYS include direct synonyms and plural/singular variations.
  - MUST EXPAND Footwear: "shoes" -> ["shoe", "sneaker", "trainer", "footwear", "kicks", "pump", "heel", "sandal"]
  - MUST EXPAND Apparel: "shirt" -> ["top", "tee", "t-shirt", "blouse", "jersey"]
`;

async function run() {
    const query = 'running shoes';
    console.log(`\nTESTING AI INTENT FOR: "${query}"`);
    
    const modelWithTools = model.bindTools([hybridSearchTool]);
    const response: any = await modelWithTools.invoke([
        { role: "system", content: systemPrompt },
        new HumanMessage(query)
    ]);

    console.log("\nTOOL CALLS:");
    response.tool_calls.forEach((tc: any) => {
        console.log(`Tool: ${tc.name}`);
        console.log(`Entity: ${tc.args.entity}`);
        console.log(`Synonyms: ${JSON.stringify(tc.args.synonyms)}`);
    });
}

run().catch(console.error).finally(() => process.exit(0));
