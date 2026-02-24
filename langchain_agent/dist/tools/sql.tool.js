"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.sqlTool = void 0;
const tools_1 = require("@langchain/core/tools");
const zod_1 = require("zod");
const database_1 = require("../config/database");
const ALLOWED_TABLES = [
    'products',
    'categories',
    'vendors',
    'master_products',
    'product_images',
    'brands',
    'users',
    'reviews',
    'product_variations',
    'variation_attributes',
    'variation_attribute_values',
    'product_variation_attribute_value'
];
exports.sqlTool = (0, tools_1.tool)(async ({ query }) => {
    try {
        const trimmedQuery = query.trim().toLowerCase();
        // Safety check: specific keywords only
        if (!trimmedQuery.startsWith('select')) {
            return "Error: Only SELECT queries are allowed.";
        }
        if (['delete', 'update', 'insert', 'drop', 'alter', 'truncate', 'grant', 'revoke'].some(keyword => trimmedQuery.includes(keyword))) {
            return "Error: Destructive or modification queries are strictly prohibited.";
        }
        console.log(`Executing SQL: ${query}`);
        const result = await database_1.db.query(query);
        return JSON.stringify(result.rows);
    }
    catch (error) {
        console.error("SQL Error:", error);
        return `Error executing query: ${error.message}`;
    }
}, {
    name: "execute_sql",
    description: "Execute a SELECT SQL query against the database to find products. Returns the results as a JSON string. Only use this for querying the 'products' table and related tables.",
    schema: zod_1.z.object({
        query: zod_1.z.string().describe("The SQL SELECT query to execute."),
    }),
});
