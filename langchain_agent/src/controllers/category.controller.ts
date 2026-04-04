import { Request, Response } from 'express';
import { embeddingsService } from '../services/embeddings.service';
import { db } from '../config/database';
import { model } from '../config/llm';
import { HumanMessage } from '@langchain/core/messages';

export const syncCategoryController = async (req: Request, res: Response) => {
    try {
        const { id, name, synonyms } = req.body;

        if (!id || !name) {
            return res.status(400).json({ status: 'error', message: 'Category ID and Name are required' });
        }

        // Logic: Generate embedding based on Name + Synonyms
        const synonymsStr = Array.isArray(synonyms) ? synonyms.join(', ') : (synonyms || '');
        const textToEmbed = `${name} | ${synonymsStr}`;
        
        console.log(`[CATEGORY_SYNC] Generating embedding for: ${textToEmbed}`);
        const embedding = await embeddingsService.generateEmbedding(textToEmbed);

        // Update database with the new embedding
        // (Laravel already updated the synonyms column, we just update the vector)
        const embeddingString = `[${embedding.join(',')}]`;
        
        await db.query(
            "UPDATE categories SET embedding = $1::vector WHERE id = $2",
            [embeddingString, id]
        );

        console.log(`[CATEGORY_SYNC] Successfully updated category ${id} embedding`);

        res.json({
            status: 'success',
            message: 'Category embedding synchronized',
            category_id: id
        });
    } catch (error: any) {
        console.error('[CATEGORY_SYNC_ERROR]', error);
        res.status(500).json({ status: 'error', message: error.message });
    }
};

export const suggestSynonymsController = async (req: Request, res: Response) => {
    const { name } = req.body;
    if (!name) return res.status(400).json({ error: "Category name is required" });

    try {
        console.log(`[SYNONYM_GEN] Generating synonyms for category: ${name}`);
        
        const prompt = `As a fashion e-commerce expert, provide 5-10 synonyms, alternative names, or highly relevant search terms for the product category: "${name}". 
        Include common variations, singular/plural forms, and colloquial terms used in Southern Africa (Zimbabwe/South Africa).
        Return ONLY a JSON array of strings. No markdown, no explanation.
        Example for "T-shirts": ["tees", "tshirts", "round neck shirts", "v-neck shirts", "tops", "summer shirts"].`;

        const response = await model.invoke([new HumanMessage(prompt)]);
        const text = response.content as string;
        
        // Clean markdown if AI includes it
        const jsonMatch = text.match(/\[.*\]/s);
        const synonyms = jsonMatch ? JSON.parse(jsonMatch[0]) : [];

        console.log(`[SYNONYM_GEN] Suggested ${synonyms.length} synonyms for ${name}`);
        res.json({ synonyms });
    } catch (error: any) {
        console.error('[SYNONYM_GEN_ERROR]', error);
        res.status(500).json({ error: error.message });
    }
};
