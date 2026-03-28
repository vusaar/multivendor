import { model } from "../config/llm";
console.log(">>>>> SEARCH AGENT LOADED FROM SOURCE <<<<<");
import { hybridSearchTool, executeHybridSearch } from "../tools/vector_search.tool";
import { HumanMessage } from "@langchain/core/messages";
import { sessionService } from "./session.service";
import { isContinuationQuery, SearchPlan } from "../utils/search_context.util";
import { embeddingsService } from "./embeddings.service";
import { searchLoggerService } from "./logger.service";
import { SEARCH_CONFIG } from "../config/search";
import crypto from 'crypto';

const systemPrompt = `You are an expert shopping assistant. 
YOUR ONLY JOB is to call the 'hybrid_product_search' tool with the precise parameters extracted from the user's query.

CRITICAL RULE: The 'query' parameter is MANDATORY. Auto-correct any obvious spelling or typographical errors from the user's raw input, and output the corrected string here (e.g. "blu snaker" -> "blue sneaker"). Our vector search engine will handle synonyms.

DATABASE MAPPING RULES (FOR PRECISION SCORING):
1. 'categories': Extract broad departments, demographics (e.g., "men", "women"), or high-level classifications. Be exact.
2. 'entity': Extract the specific core product type IN SINGULAR FORM ONLY (e.g., "shirt", "sweater", "sneaker", not "shirts"). Be exact.
3. 'synonyms': Expand the search net with 1 to 3 direct synonyms IN SINGULAR FORM. Use this EXPERT KNOWLEDGE:
   - "hoodie" -> ["sweater", "sweatshirt", "hoody"]
   - "sweater" -> ["jumper", "jersey", "pullover"]
   - "t-shirt" -> ["shirt", "tee", "top", "tshirt"]
   - "shoe" -> ["sneaker", "kick", "footwear"]
   - "trousers" -> ["pants", "jeans", "bottoms"]
   - "jacket" -> ["coat", "outerwear", "parka"]
   - "eyeshadow" -> ["cosmetics", "makeup", "palette"]
4. 'attributes': Extract modifiers (e.g., "red", "XL", "cotton").

REQUIRED TOOL PARAMETERS:
- 'query' (string, REQUIRED): The general search terms.
- 'entity' (string, optional): Specific central product type.
- 'synonyms' (array, optional): 1 to 3 direct synonyms for the core entity.
- 'categories' (array, optional): High-level classifications or demographics.
- 'attributes' (array, optional): Specific modifiers or brands.

ADDITIONAL RULES:
- ALWAYS extract precise entities. Combine the 'entity' and 'synonyms' arrays to maximize the chance of a lexical match against our database.
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
                return [];
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
            const embedding = await embeddingsService.generateEmbedding(args.query || userQuery);

            const newPlan: SearchPlan = {
                originalQuery: userQuery,
                parsedIntent: toolCall.args,
                embedding,
                pagination: { offset: 0, limit: 5 },
                timestamp: Date.now()
            };
            await sessionService.updateSession(userId, { lastSearchPlan: newPlan });

            const searchId = crypto.randomUUID();

            // Relevance Filtering & Classification Logic
            const finalResults = filterResults(results).map((r: any) => ({
                ...r,
                score: r.rrf_score // Backward compatibility for Laravel hydration
            }));

            // 3. Update message history and return
            newPlan.results = finalResults.map(r => ({ id: r.id, name: r.name, score: r.rrf_score }));
            await sessionService.updateSession(userId, { lastSearchPlan: newPlan });

            await searchLoggerService.log(
                userId, 
                userQuery, 
                toolCall.args.query || userQuery, 
                toolCall.args, 
                results, 
                Date.now() - totalStart,
                searchId
            );

            return finalResults;
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

async function fallbackSearch(query: string, userId: string): Promise<any[]> {
    const fallbackStart = Date.now();
    const searchId = crypto.randomUUID();
    console.log("[AGENT] Triggering fallback search");
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // Standard fallback: use raw query for fuzzy entity boost
    const categories = extractDemographics(query);
    const attributes = extractCommonAttributes(query);
    
    const results = await executeHybridSearch({ 
        query, 
        embedding, 
        limit: 50, 
        offset: 0,
        categories: categories.length > 0 ? categories : undefined,
        entity: query, // Broad fuzzy matching
        attributes: attributes.length > 0 ? attributes : undefined
    });

    console.log(`[TRACE] Fallback results found: ${results.length}. Top score: ${results[0]?.rrf_score}`);

    const finalResults = filterResults(results);

    const newPlan: SearchPlan = {
        originalQuery: query,
        parsedIntent: { query, categories },
        embedding: Array.from(embedding),
        pagination: { offset: 0, limit: 50 },
        timestamp: Date.now(),
        results: finalResults.map(r => ({ id: r.id, name: r.name, score: r.rrf_score }))
    };
    await sessionService.updateSession(userId, { lastSearchPlan: newPlan });

    // Log fallback search
    await searchLoggerService.log(
        userId, 
        query, 
        query, 
        { query, categories }, 
        results, 
        Date.now() - fallbackStart,
        searchId
    );

    return finalResults;
}

function filterResults(results: any[]): any[] {
    const { THRESHOLD_VERIFIED, THRESHOLD_SUGGESTION, THRESHOLD_PRECISION_LIMIT, LIMIT_VERIFIED, LIMIT_SUGGESTIONS } = SEARCH_CONFIG;

    // Filter out irrelevant
    let filtered = results.filter(r => r.rrf_score >= THRESHOLD_SUGGESTION);
    
    // Dynamic Precision: If we have a verified match, be stricter with suggestions
    const hasVerified = filtered.some(r => r.rrf_score >= THRESHOLD_VERIFIED);
    if (hasVerified) {
        filtered = filtered.filter(r => r.rrf_score >= THRESHOLD_PRECISION_LIMIT || r.rrf_score >= THRESHOLD_VERIFIED);
    }

    // Final limiting: max 15 verified + 3 suggestions (Increased for production recall)
    const verified = filtered.filter(r => r.rrf_score >= THRESHOLD_VERIFIED).slice(0, LIMIT_VERIFIED);
    const suggestions = filtered.filter(r => r.rrf_score < THRESHOLD_VERIFIED).slice(0, (verified.length > 0 ? LIMIT_SUGGESTIONS : 5));
    
    return [...verified, ...suggestions];
}

function extractDemographics(query: string): string[] {
    const categories: string[] = [];
    const q = query.toLowerCase();
    
    if (/\b(men|gents|male|boys|man)\b/i.test(q)) categories.push("men");
    if (/\b(women|ladies|female|girls|woman)\b/i.test(q)) categories.push("women");
    return categories;
}

function extractCommonAttributes(query: string): string[] {
    const attributes: string[] = [];
    const q = query.toLowerCase();
    const colors = ["blue", "red", "black", "white", "green", "pink", "yellow"];
    
    colors.forEach(color => {
        if (new RegExp(`\\b${color}\\b`, 'i').test(q)) attributes.push(color);
    });
    
    return attributes;
}
