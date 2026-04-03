import { model } from "../config/llm";
console.log(">>>>> SEARCH AGENT LOADED FROM SOURCE <<<<<");
import { hybridSearchTool, executeHybridSearch } from "../tools/vector_search.tool";
import { HumanMessage } from "@langchain/core/messages";
import { sessionService } from "./session.service";
import { isContinuationQuery, SearchPlan } from "../utils/search_context.util";

export interface SearchResponse {
    products: any[];
    total: number;
}
import { embeddingsService } from "./embeddings.service";
import { searchLoggerService } from "./logger.service";
import { SEARCH_CONFIG } from "../config/search";
import crypto from 'crypto';
import { categoryGuideService } from "./category.guide.service";

const systemPrompt = `You are a helpful AI shopping assistant for a multi-vendor storefront.

Your role is to help users find products. However, you must distinguish between a **Product Search** and **General Conversation**.

### 1. PRODUCT SEARCH RULES
- If the user is looking for an item (e.g., "blue shirt", "sneakers"), you MUST call the 'hybrid_product_search' tool.
- The 'query' parameter is MANDATORY. Auto-correct spelling (e.g., "blu snaker" -> "blue sneaker").
- DISTINGUISH between product types (e.g., 'shirt' vs. 't-shirt').
- EXTRACT precise entities (singular form), synonyms, and attributes.

### 2. GREETING & GENERAL CONVERSATION RULES (CRITICAL)
- **DO NOT CALL ANY TOOLS** if the user is just saying hello, greeting you, or asking how you are.
- **DO NOT CALL ANY TOOLS** if the user asks "What can you do?" or "Help".
- Instead, respond politely and explain how to search.
- **Example Response:** "Hello! I'm your AI shopping assistant. I can help you find anything in our catalog. Try searching for 'black cotton shirt' or 'nike shoes'. What can I find for you?"

### 3. TAXONOMY MAPPING (MANDATORY)
- **Primary Goal**: Map the user's intent to the most specific category slug provided in the "AVAILABLE CATEGORY SLUGS" section.
- **Synonyms (MANDATORY EXPANSION)**: Even when using a slug, you MUST extract colloquial synonyms into the 'synonyms' parameter to catch unique names:
  - Footwear: "shoes" -> ["shoe", "sneaker", "trainer", "footwear", "kicks"]
  - Apparel: "sweater" -> ["jumper", "pullover", "cardigan"]
  - Apparel: "shirt" -> ["top", "tee", "t-shirt", "blouse", "jersey"]
  - Apparel: "bottom" -> ["trouser", "pant", "jeans", "skirt", "short"]
- **Demographics**: Always extract "men" or "women" into the 'categories' array if mentioned or implied (e.g., "for her" -> "women").
- **Attributes**: Extract colors, brands, and materials normally.

### 4. CONTEXTUAL INTENT MAPPING (CRITICAL)
- Map vague human context to specific product types to improve semantic retrieval:
  - "winter", "cold", "warm": -> query="sweater", synonyms=["jumper", "jacket", "hoodie", "knitwear"]
  - "work", "office", "professional": -> query="blouse", synonyms=["shirt", "formal top", "trousers", "vest"]
  - "wedding", "party", "date night": -> query="dress", synonyms=["gown", "skirt", "fragrance", "perfume"]
  - "summer", "beach", "hot": -> query="t-shirt", synonyms=["vest", "shorts", "sandals", "top"]

### 5. EXAMPLES
- User: "jumper for him" -> [Call hybrid_product_search(query="jumper", target_category_slug="men-tops", synonyms=["sweater", "pullover"], categories=["men"])]
- User: "something warm for winter" -> [Call hybrid_product_search(query="sweater", synonyms=["jumper", "jacket", "hoodie", "knitwear"])]
- User: "professional office top for her" -> [Call hybrid_product_search(query="blouse", synonyms=["shirt", "formal top"], categories=["women"])]
- User: "how are you?" -> Assistant: "I'm doing great, thank you! I'm ready to help you shop. What are you looking for?"
`;

const toolsByName: Record<string, any> = {
    hybrid_product_search: hybridSearchTool,
};


export const processUserQuery = async (userQuery: string, userId: string = "default", page: number = 1): Promise<SearchResponse> => {
    const totalStart = Date.now();
    console.log(`[AGENT] Starting stateful router for: "${userQuery}" (User: ${userId})`);

    try {
        // --- Tier 0: Quick Greeting Filter ---
        const greetingRegex = /^\s*(hello|hi|hey|hola|greetings|how are you|good morning|good afternoon|good evening)\b\s*[!?.]*$/i;
        if (greetingRegex.test(userQuery)) {
            console.log(`[AGENT] Greeting detected via pre-filter: "${userQuery}"`);
            return {
                products: [{ 
                    id: "AI_MESSAGE", 
                    name: "ASSISTANT", 
                    text: "Hello! I'm your AI shopping assistant. I can help you find anything in our catalog. Try searching for something specific like 'black cotton shirt' or 'nike shoes'. What can I find for you?" 
                }],
                total: 1
            };
        }

        const session = await sessionService.getSession(userId);

        // --- Tier 1: Continuation/Pagination Bypass ---
        const isRepeat = session.lastSearchPlan && userQuery.toLowerCase() === session.lastSearchPlan.originalQuery.toLowerCase();

        if ((isContinuationQuery(userQuery) || isRepeat) && session.lastSearchPlan) {
            console.log(`[ROUTER] Bypass triggered (Continuation: ${isContinuationQuery(userQuery)}, Repeat: ${isRepeat}).`);
            const plan: SearchPlan = session.lastSearchPlan;

            // Handle hidden suggestions if user said "Yes"
            if (plan.pendingSuggestions && isContinuationQuery(userQuery)) {
                console.log(`[ROUTER] Displaying ${plan.pendingSuggestions.length} pending suggestions.`);
                const suggestions = plan.pendingSuggestions;
                plan.pendingSuggestions = undefined; // Clear it
                await sessionService.updateSession(userId, { lastSearchPlan: plan });
                return { products: suggestions, total: suggestions.length };
            }

            // Return a larger set (50) so Laravel's secondary pagination has data to work with
            const results = await executeHybridSearch({
                ...plan.parsedIntent,
                embedding: plan.embedding,
                limit: SEARCH_CONFIG.LIMIT_VERIFIED,
                offset: (page - 1) * SEARCH_CONFIG.LIMIT_VERIFIED
            });

            if (results.length > 0) {
                console.log(`[PERF] Bypass search took: ${Date.now() - totalStart}ms`);
                return { products: results, total: Number(results[0]?.total_count || 0) };
            } else {
                console.log(`[ROUTER] No results found in bypass.`);
                return { products: [], total: 0 };
            }
        }

        // --- Tier 2: New Search (Standard Path) ---
        const categorySnippet = await categoryGuideService.getPromptSnippet();
        const dynamicPrompt = `${systemPrompt}\n\n${categorySnippet}`;

        const modelWithTools = model.bindTools([hybridSearchTool]);
        const reasoningStart = Date.now();

        const timeoutPromise = new Promise((_, reject) => {
            setTimeout(() => reject(new Error("Timeout")), 30000);
        });

        const modelPromise = modelWithTools.invoke([
            { role: "system", content: dynamicPrompt },
            { role: "user", content: "hi" },
            { role: "assistant", content: "Hello! I'm your AI shopping assistant. I can help you find anything in our catalog. Try searching for 'black cotton shirt' or 'nike shoes'. What can I find for you?" },
            { role: "user", content: "how are you?" },
            { role: "assistant", content: "I'm doing great, thank you! I'm ready to help you shop. What are you looking for?" },
            { role: "user", content: "what do you sell?" },
            { role: "assistant", content: "I sell a wide range of fashion items from various vendors—including shirts, shoes, dresses, and more. Try searching for something specific like 'floral dress'!" },
            new HumanMessage(userQuery)
        ]);

        const response: any = await Promise.race([modelPromise, timeoutPromise]);
        console.log(`[PERF] Initial reasoning took: ${Date.now() - reasoningStart}ms`);
        console.log(`[AGENT] Model Response Tool Calls:`, response.tool_calls);
        const toolCalls = response.tool_calls || [];
        if (toolCalls.length === 0) {
            console.log("[AGENT] No tools called. Checking for AI message.");
            const content = response.content;
            if (content && typeof content === 'string' && content.trim().length > 0) {
                console.log("[AGENT] Returning direct AI message.");
                return {
                    products: [{ id: "AI_MESSAGE", name: "ASSISTANT", text: content }],
                    total: 1
                };
            }
            console.log("[AGENT] No message, falling back to simple query.");
            return fallbackSearch(userQuery, userId);
        }

        const toolCall = toolCalls[0];
        if (toolCall.name === "hybrid_product_search") {
            const args = { ...toolCall.args, limit: SEARCH_CONFIG.LIMIT_VERIFIED, offset: (page - 1) * SEARCH_CONFIG.LIMIT_VERIFIED };
            console.log(`[AGENT] Executing hybrid_product_search with ARGS:`, JSON.stringify(args, null, 2));
            
            const toolResult = await hybridSearchTool.invoke(args) as string;
            const results = JSON.parse(toolResult);

            if (results.status === "error") {
                console.error("[AGENT] Tool execution error:", results.message);
                return fallbackSearch(userQuery, userId);
            }

            // If it returned { status: "no_results", ... }, we handle it
            if (results.status === "no_results") {
                console.log(`[AGENT] Tool returned no results.`);
                return { products: [], total: 0 };
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
            const bucketed = filterResults(results);
            
            const IMAGE_BASE_URL = 'https://store.eyamisolutions.co.zw/storage/';

            const finalResults = bucketed.results.map((r: any) => ({
                ...r,
                image: r.image_path ? (r.image_path.startsWith('http') ? r.image_path : `${IMAGE_BASE_URL}${r.image_path}`) : null,
                score: r.rrf_score // Backward compatibility for Laravel hydration
            }));
            console.log(`[AGENT] Partitioning complete. Matches: ${bucketed.results.length}, Suggestions: ${bucketed.suggestions.length}`);

            // 3. Update message history and return
            const finalPlan: SearchPlan = {
                originalQuery: userQuery,
                parsedIntent: toolCall.args,
                embedding,
                pagination: { offset: 0, limit: 5 },
                timestamp: Date.now(),
                results: finalResults.map(r => ({ id: r.id, name: r.name, score: r.rrf_score, image: r.image }))
            };
            await sessionService.updateSession(userId, { lastSearchPlan: finalPlan });

            await searchLoggerService.log(
                userId, 
                userQuery, 
                toolCall.args.query || userQuery, 
                toolCall.args, 
                results, 
                Date.now() - totalStart,
                searchId
            );

            return { products: finalResults, total: Number(results[0]?.total_count || 0) };
        }

        // Handle other tools (like category search) if needed
        const tool = toolsByName[toolCall.name];
        if (!tool) return fallbackSearch(userQuery, userId, page);

        const toolResult = await tool.invoke({ ...toolCall.args, page });
        const toolResults = JSON.parse(toolResult);
        return { products: toolResults, total: toolResults.length };

    } catch (error: any) {
        console.error("[AGENT] Router Error:", error.message);
        return fallbackSearch(userQuery, userId, page);
    }
};

async function fallbackSearch(query: string, userId: string, page: number = 1): Promise<SearchResponse> {
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
        limit: SEARCH_CONFIG.LIMIT_VERIFIED, 
        offset: (page - 1) * SEARCH_CONFIG.LIMIT_VERIFIED,
        categories: categories.length > 0 ? categories : undefined,
        entity: query, // Broad fuzzy matching
        attributes: attributes.length > 0 ? attributes : undefined
    });

    console.log(`[TRACE] Fallback results found: ${results.length}. Top score: ${results[0]?.rrf_score}`);

    const IMAGE_BASE_URL = 'https://store.eyamisolutions.co.zw/storage/';
    const bucketed = filterResults(results);
    const finalResults = bucketed.results.map((r: any) => ({
        ...r,
        image: r.image_path ? (r.image_path.startsWith('http') ? r.image_path : `${IMAGE_BASE_URL}${r.image_path}`) : null,
        score: r.rrf_score
    }));

    const newPlan: SearchPlan = {
        originalQuery: query,
        parsedIntent: { query, categories },
        embedding: Array.from(embedding),
        pagination: { offset: 0, limit: SEARCH_CONFIG.LIMIT_VERIFIED },
        timestamp: Date.now(),
        results: finalResults.map(r => ({ id: r.id, name: r.name, score: r.rrf_score, image: r.image }))
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

    return { products: finalResults, total: Number(results[0]?.total_count || 0) };
}

function filterResults(results: any[]): { results: any[], suggestions: any[], hasOnlySuggestions: boolean } {
    const { THRESHOLD_VERIFIED, THRESHOLD_SUGGESTION, THRESHOLD_PRECISION_LIMIT, LIMIT_VERIFIED, LIMIT_SUGGESTIONS } = SEARCH_CONFIG;

    // Filter out irrelevant
    const filtered = results.filter(r => r.rrf_score >= THRESHOLD_SUGGESTION);
    
    const verified = filtered.filter(r => r.rrf_score >= THRESHOLD_VERIFIED).slice(0, LIMIT_VERIFIED);
    
    // Dynamic Precision for suggestions
    let suggestions = filtered.filter(r => r.rrf_score < THRESHOLD_VERIFIED);
    if (verified.length > 0) {
        // If we have verified, be stricter with suggestions
        suggestions = suggestions.filter(r => r.rrf_score >= THRESHOLD_PRECISION_LIMIT);
    }
    
    const limitedSuggestions = suggestions.slice(0, (verified.length > 0 ? LIMIT_SUGGESTIONS : 5));
    
    const hasOnlySuggestions = verified.length === 0 && limitedSuggestions.length > 0;

    return {
        results: [...verified, ...limitedSuggestions],
        suggestions: limitedSuggestions,
        hasOnlySuggestions
    };
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
