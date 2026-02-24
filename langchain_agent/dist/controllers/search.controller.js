"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.searchController = void 0;
const search_agent_1 = require("../services/search.agent");
const searchController = async (req, res) => {
    try {
        console.log('Search Controller Request Body:', req.body);
        const { query, userId } = req.body || {};
        if (!query) {
            return res.status(400).json({ status: 'error', message: 'Query is required' });
        }
        const threadId = userId || 'default_user';
        console.log(`Processing query for ${threadId}: ${query}`);
        const response = await (0, search_agent_1.processUserQuery)(query, threadId);
        return res.json({
            status: 'success',
            data: {
                results: response,
            }
        });
    }
    catch (error) {
        console.error('Search Controller Error:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Internal Server Error',
            error: error.message
        });
    }
};
exports.searchController = searchController;
