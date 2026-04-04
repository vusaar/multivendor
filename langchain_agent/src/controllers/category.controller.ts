import { Request, Response } from 'express';
import { embeddingsService } from '../services/embeddings.service';
import { db } from '../config/database';

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
