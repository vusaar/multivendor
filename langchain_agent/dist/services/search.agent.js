"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.processUserQuery = void 0;
const llm_1 = require("../config/llm");
const vector_search_tool_1 = require("../tools/vector_search.tool");
const messages_1 = require("@langchain/core/messages");
const session_service_1 = require("./session.service");
const search_context_util_1 = require("../utils/search_context.util");
const embeddings_service_1 = require("./embeddings.service");
const logger_service_1 = require("./logger.service");
const systemPrompt = `You are an expert shopping assistant. 
YOUR ONLY JOB is to call the 'hybrid_product_search' tool with the correct parameters extracted from the user's query.

CRITICAL RULE: The 'query' parameter is MANDATORY. Always include the original or slightly cleaned user query in the 'query' field, even if you have extracted entities, attributes, or categories.

DATABASE MAPPING RULES:
1. 'categories': Use for departments, demographics (men/women), or high-level classifications. 
   Rule: Identify ANY high-level classification (gender, department, etc.) and include it in 'categories'.
   Example: "for men" -> categories: ["men"]
2. 'entity': Use for the specific product type.
   Examples: "tshirt", "mascara", "smartwatch", "drill", "football".
3. 'attributes': Use for technical specs, color, size, brand, or material.
   Examples: "blue", "XL", "waterproof", "nike", "leather".

REQUIRED TOOL PARAMETERS:
- 'query' (string, REQUIRED): The search terms.
- 'entity' (string, optional): Specific product type.
- 'categories' (array, optional): High-level classifications.
- 'attributes' (array, optional): Specific specs or brands.

EXAMPLES:
User: "black nike running shoes for men under 100"
Tool: { "query": "nike running shoes", "entity": "shoes", "categories": ["men"], "attributes": ["black", "nike", "running"], "max_price": 100 }

User: "ladies perfumes"
Tool: { "query": "perfumes", "entity": "perfume", "categories": ["women"] }

ADDITIONAL RULES:
- ALWAYS expand synonyms (gents -> men, ladies -> women).
- If a high-level department (e.g., beauty, electronics) is mentioned or implied, include it in 'categories'.
- ALWAYS call the tool. NEVER respond with text first.`;
const toolsByName = {
    hybrid_product_search: vector_search_tool_1.hybridSearchTool,
};
const processUserQuery = async (userQuery, userId = "default") => {
    const totalStart = Date.now();
    console.log(`[AGENT] Starting stateful router for: "${userQuery}" (User: ${userId})`);
    try {
        const session = await session_service_1.sessionService.getSession(userId);
        // --- Tier 1: Continuation/Pagination Bypass ---
        const isRepeat = session.lastSearchPlan && userQuery.toLowerCase() === session.lastSearchPlan.originalQuery.toLowerCase();
        if (((0, search_context_util_1.isContinuationQuery)(userQuery) || isRepeat) && session.lastSearchPlan) {
            console.log(`[ROUTER] Bypass triggered (Continuation: ${(0, search_context_util_1.isContinuationQuery)(userQuery)}, Repeat: ${isRepeat}).`);
            const plan = session.lastSearchPlan;
            // Return a larger set (50) so Laravel's secondary pagination has data to work with
            const results = await (0, vector_search_tool_1.executeHybridSearch)({
                ...plan.parsedIntent,
                embedding: plan.embedding,
                limit: 50,
                offset: 0
            });
            if (results.length > 0) {
                console.log(`[PERF] Bypass search took: ${Date.now() - totalStart}ms`);
                return results;
            }
            else {
                console.log(`[ROUTER] No results found in bypass.`);
                return { status: "no_results", message: "No products found." };
            }
        }
        // --- Tier 2: New Search (Standard Path) ---
        const modelWithTools = llm_1.model.bindTools([vector_search_tool_1.hybridSearchTool]);
        const reasoningStart = Date.now();
        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error("Timeout")), 30000);
        });
        const modelPromise = modelWithTools.invoke([
            { role: "system", content: systemPrompt + "\nCRITICAL: You must call a tool. Do NOT answer from memory." },
            new messages_1.HumanMessage(userQuery)
        ]);
        const response = await Promise.race([modelPromise, timeoutPromise]);
        console.log(`[PERF] Initial reasoning took: ${Date.now() - reasoningStart}ms`);
        console.log(`[AGENT] Model Response Tool Calls:`, response.tool_calls);
        const toolCalls = response.tool_calls || [];
        if (toolCalls.length === 0) {
            console.log("[AGENT] No tools called. Falling back to simple query.");
            return fallbackSearch(userQuery, userId);
        }
        const toolCall = toolCalls[0];
        if (toolCall.name === "hybrid_product_search") {
            const args = { ...toolCall.args, limit: 50, offset: 0 };
            console.log(`[AGENT] Executing hybrid_product_search via tool.invoke with limit 50.`);
            // We call the tool's invoke method. This will trigger the tool's internal func, 
            // which contains the console.log at line 183 of vector_search.tool.ts.
            const toolResult = await vector_search_tool_1.hybridSearchTool.invoke(args);
            const results = JSON.parse(toolResult);
            if (results.status === "error") {
                console.error("[AGENT] Tool execution error:", results.message);
                return fallbackSearch(userQuery, userId);
            }
            // If it returned { status: "no_results", ... }, we handle it
            if (results.status === "no_results") {
                console.log(`[AGENT] Tool returned no results.`);
                return [];
            }
            // We still need the embedding for stateful pagination/bypass later.
            // The tool generated it, but it's not returning it in the results.
            // For now, we'll re-generate it to store the plan, or optimize later.
            const embedding = await embeddings_service_1.embeddingsService.generateEmbedding(args.query || userQuery);
            const newPlan = {
                originalQuery: userQuery,
                parsedIntent: toolCall.args,
                embedding,
                pagination: { offset: 0, limit: 5 },
                timestamp: Date.now()
            };
            await session_service_1.sessionService.updateSession(userId, { lastSearchPlan: newPlan });
            console.log(`[PERF] Total processUserQuery took: ${Date.now() - totalStart} ms`);
            // Log search asynchronously
            logger_service_1.searchLoggerService.log(userId, userQuery, toolCall.args, results, Date.now() - totalStart);
            return results;
        }
        // Handle other tools (like category search) if needed
        const tool = toolsByName[toolCall.name];
        if (!tool)
            return fallbackSearch(userQuery, userId);
        const toolResult = await tool.invoke(toolCall.args);
        return JSON.parse(toolResult);
    }
    catch (error) {
        console.error("[AGENT] Router Error:", error.message);
        return fallbackSearch(userQuery, userId);
    }
};
exports.processUserQuery = processUserQuery;
async function fallbackSearch(query, userId) {
    const fallbackStart = Date.now();
    console.log("[AGENT] Triggering fallback search");
    const embedding = await embeddings_service_1.embeddingsService.generateEmbedding(query);
    // Safety net: Extract demographics even in fallback
    const categories = extractDemographics(query);
    const results = await (0, vector_search_tool_1.executeHybridSearch)({
        query,
        embedding,
        limit: 50,
        offset: 0,
        categories: categories.length > 0 ? categories : undefined
    });
    const newPlan = {
        originalQuery: query,
        parsedIntent: { query, categories },
        embedding,
        pagination: { offset: 0, limit: 10 },
        timestamp: Date.now()
    };
    await session_service_1.sessionService.updateSession(userId, { lastSearchPlan: newPlan });
    // Log fallback search
    logger_service_1.searchLoggerService.log(userId, query, { query, categories }, results, Date.now() - fallbackStart);
    return results;
}
function extractDemographics(query) {
    const categories = [];
    const q = query.toLowerCase();
    // Use word boundaries or specific patterns to avoid "men" matching "women"
    if (/\b(men|gents|male|boys|man)\b/i.test(q)) {
        categories.push("men");
    }
    if (/\b(women|ladies|female|girls|woman)\b/i.test(q)) {
        categories.push("women");
    }
    return categories;
}
