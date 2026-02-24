import { model } from './src/config/llm';
import { HumanMessage } from "@langchain/core/messages";
import { sqlTool } from "./src/tools/sql.tool";
import { hybridSearchTool, categorySearchTool } from "./src/tools/vector_search.tool";

const systemPrompt = `You are a shopping assistant.
Find products using these tools:
- hybrid_product_search: Use for name, category, OR price searches. It supports optional min_price and max_price.
  CRITICAL: If price is mentioned, extract it and pass to min_price or max_price. DO NOT include price in the query string.
  EXAMPLE: "shirts under 20" -> hybrid_product_search(query="shirts", max_price=20)
- execute_sql: MANDATORY for complex variation-specific sizes (e.g. "size XL").
... (rest of prompt shortened for test) ...`;

async function testRaw() {
    const userQuery = "ladies tops for less than $10";
    const modelWithTools = model.bindTools([sqlTool, hybridSearchTool, categorySearchTool]);
    const response = await modelWithTools.invoke([
        { role: "system", content: systemPrompt },
        new HumanMessage(userQuery)
    ]);
    console.log("Raw Response Tool Calls:", JSON.stringify(response.tool_calls, null, 2));
}

testRaw().then(() => process.exit(0));
