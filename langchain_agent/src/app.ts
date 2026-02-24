import express from 'express';
import cors from 'cors';
import bodyParser from 'body-parser';
import apiRoutes from './routes/api.routes';

const app = express();

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Body error handler
app.use((err: any, req: any, res: any, next: any) => {
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
app.use('/api', apiRoutes);

// Health check
app.get('/', (req, res) => {
    res.send('LangChain SQL Agent is running');
});

// Global error handler for app
app.use((err: any, req: any, res: any, next: any) => {
    console.error('App Error Handler:', err);
    res.status(500).json({ status: 'error', message: err.message });
});

export default app;
