import { Request, Response } from 'express';
import { processUserQuery } from '../services/search.agent';

export const searchController = async (req: Request, res: Response) => {
    try {
        console.log('Search Controller Request Body:', req.body);
        const { query, userId } = req.body || {};

        if (!query) {
            return res.status(400).json({ status: 'error', message: 'Query is required' });
        }

        const threadId = userId || 'default_user';
        console.log(`Processing query for ${threadId}: ${query}`);

        const response = await processUserQuery(query, threadId);

        return res.json({
            status: 'success',
            data: {
                results: response,
            }
        });
    } catch (error: any) {
        console.error('Search Controller Error:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Internal Server Error',
            error: error.message
        });
    }
};
