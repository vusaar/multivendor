"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const search_agent_1 = require("./services/search.agent");
const dotenv_1 = __importDefault(require("dotenv"));
dotenv_1.default.config();
async function runTest() {
    const query = "Show me active products in the Electronics category";
    console.log(`Test Query: ${query}`);
    try {
        const response = await (0, search_agent_1.processUserQuery)(query);
        console.log("Response:", response);
    }
    catch (error) {
        console.error("Test Failed:", error);
    }
}
runTest();
