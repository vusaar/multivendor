"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const app_1 = __importDefault(require("./app"));
const dotenv_1 = __importDefault(require("dotenv"));
const database_1 = require("./config/database");
dotenv_1.default.config();
const PORT = process.env.PORT || 3000;
// Test DB connection before starting
database_1.db.pool.connect()
    .then(client => {
    console.log('Connected to Database');
    client.release();
    const server = app_1.default.listen(PORT, () => {
        console.log(`Server is running on http://localhost:${PORT}`);
    });
    server.on('request', (req) => {
        console.log(`Raw request: ${req.method} ${req.url}`);
    });
    server.on('error', (err) => {
        console.error('Server error:', err);
    });
    server.on('close', () => {
        console.log('Server closed');
    });
})
    .catch(err => {
    console.error('Failed to connect to database', err);
    process.exit(1);
});
// Handle global errors
process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});
process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
});
