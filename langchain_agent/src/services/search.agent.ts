import { model } from "../config/llm";
import { hybridSearchTool, executeHybridSearch } from "../tools/vector_search.tool";
import { HumanMessage } from "@langchain/core/messages";
import { sessionService } from "./session.service";
import { isContinuationQuery, SearchPlan } from "../utils/search_context.util";
import { embeddingsService } from "./embeddings.service";

const systemPrompt = `You are an expert shopping assistant. 
YOUR ONLY JOB is to call the 'hybrid_product_search' tool with the correct parameters extracted from the user's query.

DATABASE MAPPING RULES:
1. 'categories': Use for departments, demographics, or high-level classifications. 
   Examples: "men", "women", "kids", "beauty", "electronics", "home & garden", "sports".
   Rule: Identify ANY high-level classification (gender, department, etc.) and include it in 'categories'.
2. 'entity': Use for the specific product type.
   Examples: "tshirt", "mascara", "smartwatch", "drill", "football".
3. 'attributes': Use for technical specs, color, size, brand, or material.
   Examples: "blue", "XL", "waterproof", "nike", "leather".

EXAMPLES:
- User: "gents tshirt" -> categories=["men", "gents"], entity="tshirt"
- User: "beauty product" -> categories=["beauty"]
- User: "luxury mascara" -> categories=["beauty"], entity="mascara", attributes=["luxury"]
- User: "waterproof sports watch" -> categories=["sports"], entity="watch", attributes=["waterproof"]
- User: "blue jeans for women" -> categories=["women"], entity="jeans", attributes=["blue"]

CRITICAL: 
- ALWAYS expand synonyms (gents -> men, ladies -> women).
- If a high-level department (e.g., beauty, electronics) is mentioned or implied, include it in 'categories'.
- ALWAYS call the tool. NEVER respond with text first.`;

const toolsByName: Record<string, any> = {
    hybrid_product_search: hybridSearchTool,
};

export const processUserQuery = async (userQuery: string, userId: string = "default") => {
    const totalStart = Date.now();
    console.log(`[AGENT] Starting stateful router for: "${userQuery}" (User: ${userId})`);

    try {
        const session = await sessionService.getSession(userId);

        // --- Tier 1: Continuation/Pagination Bypass ---
        const isRepeat = session.lastSearchPlan && userQuery.toLowerCase() === session.lastSearchPlan.originalQuery.toLowerCase();

        if ((isContinuationQuery(userQuery) || isRepeat) && session.lastSearchPlan) {
            console.log(`[ROUTER] Bypass triggered (Continuation: ${isContinuationQuery(userQuery)}, Repeat: ${isRepeat}).`);
            const plan: SearchPlan = session.lastSearchPlan;

            // Return a larger set (50) so Laravel's secondary pagination has data to work with
            const results = await executeHybridSearch({
                ...plan.parsedIntent,
                embedding: plan.embedding,
                limit: 50,
                offset: 0
            });

            if (results.length > 0) {
                console.log(`[PERF] Bypass search took: ${Date.now() - totalStart}ms`);
                return results;
            } else {
                console.log(`[ROUTER] No results found in bypass.`);
                return { status: "no_results", message: "No products found." };
            }
        }

        // --- Tier 2: New Search (Standard Path) ---
        const modelWithTools = model.bindTools([hybridSearchTool]);
        const reasoningStart = Date.now();

        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error("Timeout")), 30000);
        });

        const modelPromise = modelWithTools.invoke([
            { role: "system", content: systemPrompt + "\nCRITICAL: You must call a tool. Do NOT answer from memory." },
            new HumanMessage(userQuery)
        ]);

        const response: any = await Promise.race([modelPromise, timeoutPromise]);
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
            const toolResult = await hybridSearchTool.invoke(args) as string;
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
            const embedding = await embeddingsService.generateEmbedding(args.query || userQuery);

            const newPlan: SearchPlan = {
                originalQuery: userQuery,
                parsedIntent: toolCall.args,
                embedding,
                pagination: { offset: 0, limit: 5 },
                timestamp: Date.now()
            };
            await sessionService.updateSession(userId, { lastSearchPlan: newPlan });

            console.log(`[PERF] Total processUserQuery took: ${Date.now() - totalStart} ms`);
            return results;
        }

        // Handle other tools (like category search) if needed
        const tool = toolsByName[toolCall.name];
        if (!tool) return fallbackSearch(userQuery, userId);

        const toolResult = await tool.invoke(toolCall.args);
        return JSON.parse(toolResult);

    } catch (error: any) {
        console.error("[AGENT] Router Error:", error.message);
        return fallbackSearch(userQuery, userId);
    }
};

async function fallbackSearch(query: string, userId: string) {
    console.log("[AGENT] Triggering fallback search");
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // Safety net: Extract demographics even in fallback
    const categories = extractDemographics(query);
    
    const results = await executeHybridSearch({ 
        query, 
        embedding, 
        limit: 50, 
        offset: 0,
        categories: categories.length > 0 ? categories : undefined
    });

    const newPlan: SearchPlan = {
        originalQuery: query,
        parsedIntent: { query, categories },
        embedding,
        pagination: { offset: 0, limit: 10 },
        timestamp: Date.now()
    };
    await sessionService.updateSession(userId, { lastSearchPlan: newPlan });

    return results;
}

function extractDemographics(query: string): string[] {
    const categories: string[] = [];
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
