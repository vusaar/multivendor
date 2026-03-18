import { Request, Response } from 'express';
import { embeddingsService } from '../services/embeddings.service';

/**
 * Handles embedding generation for a product text.
 * Expected body: { text: string }
 */
export const generateEmbeddingController = async (req: Request, res: Response) => {
    try {
        const { text } = req.body;

        if (!text) {
            return res.status(400).json({ status: 'error', message: 'Text is required' });
        }

        const embedding = await embeddingsService.generateEmbedding(text);

        return res.json({
            status: 'success',
            embedding
        });
    } catch (error: any) {
        console.error('Error in generateEmbeddingController:', error);
        return res.status(500).json({ status: 'error', message: error.message });
    }
};

/**
 * Formats and generates embeddings for a raw product object.
 * Expected body: { product: any }
 */
export const formatAndGenerateEmbeddingController = async (req: Request, res: Response) => {
    try {
        const { product } = req.body;

        if (!product) {
            return res.status(400).json({ status: 'error', message: 'Product data is required' });
        }

        const formattedText = embeddingsService.formatProductForEmbedding(product);
        const embedding = await embeddingsService.generateEmbedding(formattedText);

        return res.json({
            status: 'success',
            formatted_text: formattedText,
            embedding
        });
    } catch (error: any) {
        console.error('Error in formatAndGenerateEmbeddingController:', error);
        return res.status(500).json({ status: 'error', message: error.message });
    }
};
