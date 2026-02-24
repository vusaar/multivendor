"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const cors_1 = __importDefault(require("cors"));
const body_parser_1 = __importDefault(require("body-parser"));
const api_routes_1 = __importDefault(require("./routes/api.routes"));
const app = (0, express_1.default)();
app.use((0, cors_1.default)());
app.use(body_parser_1.default.json());
app.use(body_parser_1.default.urlencoded({ extended: true }));
// Body error handler
app.use((err, req, res, next) => {
    if (err instanceof SyntaxError && 'body' in err) {
        console.error('JSON Syntax Error in body:', err.message);
        return res.status(400).send({ status: 'error', message: 'Invalid JSON' });
    }
    next();
});
// Main logger
app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.url}`);
    if (req.body) {
        console.log('Processed Body Keys:', Object.keys(req.body));
    }
    next();
});
// Routes
app.use('/api', api_routes_1.default);
// Health check
app.get('/', (req, res) => {
    res.send('LangChain SQL Agent is running');
});
// Global error handler for app
app.use((err, req, res, next) => {
    console.error('App Error Handler:', err);
    res.status(500).json({ status: 'error', message: err.message });
});
exports.default = app;
