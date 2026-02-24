"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.processUserQuery = void 0;
const llm_1 = require("../config/llm");
const sql_tool_1 = require("../tools/sql.tool");
const vector_search_tool_1 = require("../tools/vector_search.tool");
const messages_1 = require("@langchain/core/messages");
const systemPrompt = `You are a shopping assistant.
Find products using these tools:
- hybrid_product_search: Use for name, category, OR price searches. 
- execute_sql: MANDATORY for complex variation-specific sizes (e.g. "size XL").

Rules:
1. ALWAYS respond in valid JSON.
2. Return: [{"id": "...", "name": "...", "price": "..."}]
3. If not found: {"status": "no_results", "message": "..."}
4. NO conversational text.`;
const toolsByName = {
    execute_sql: sql_tool_1.sqlTool,
    hybrid_product_search: vector_search_tool_1.hybridSearchTool,
    search_categories_semantically: vector_search_tool_1.categorySearchTool,
};
const processUserQuery = async (userQuery, _threadId = "default") => {
    const totalStart = Date.now();
    console.log(`[AGENT] Starting custom router for: "${userQuery}"`);
    try {
        // 1. Initial reasoning step (Single Model Call)
        const modelWithTools = llm_1.model.bindTools([sql_tool_1.sqlTool, vector_search_tool_1.hybridSearchTool, vector_search_tool_1.categorySearchTool]);
        const reasoningStart = Date.now();
        // Race the model call against a timeout
        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error("Timeout")), 30000);
        });
        const modelPromise = modelWithTools.invoke([
            { role: "system", content: systemPrompt },
            new messages_1.HumanMessage(userQuery)
        ]);
        const response = await Promise.race([modelPromise, timeoutPromise]);
        console.log(`[PERF] Initial reasoning took: ${Date.now() - reasoningStart}ms`);
        console.log("[AGENT] Raw Response from Google:", JSON.stringify(response, null, 2));
        // 2. Extract tool calls
        const toolCalls = response.tool_calls || [];
        if (toolCalls.length === 0) {
            console.log("[AGENT] No tools called, parsing direct response");
            return parseResponse(response.content, userQuery);
        }
        // 3. Execute first tool call (usually enough for search)
        const toolCall = toolCalls[0];
        const tool = toolsByName[toolCall.name];
        if (!tool) {
            console.warn(`[AGENT] Tool ${toolCall.name} not found, falling back`);
            return fallbackSearch(userQuery);
        }
        console.log(`[AGENT] Executing tool: ${toolCall.name} `);
        const toolResult = await tool.invoke(toolCall.args);
        console.log(`[PERF] Total processUserQuery took: ${Date.now() - totalStart} ms`);
        return JSON.parse(toolResult);
    }
    catch (error) {
        console.error("[AGENT] Router Error:", error.message);
        return fallbackSearch(userQuery);
    }
};
exports.processUserQuery = processUserQuery;
async function fallbackSearch(query) {
    console.log("[AGENT] Triggering fallback search");
    const result = await vector_search_tool_1.hybridSearchTool.invoke({ query, limit: 10 });
    return JSON.parse(result);
}
function parseResponse(content, userQuery) {
    const jsonMatch = content.match(/(\[[\s\S]*\]|\{[\s\S]*\})/);
    const jsonStr = jsonMatch ? jsonMatch[0] : content;
    try {
        return JSON.parse(jsonStr);
    }
    catch (e) {
        console.warn("[AGENT] JSON Parse failed, falling back");
        return fallbackSearch(userQuery);
    }
}
