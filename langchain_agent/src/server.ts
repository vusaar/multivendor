import app from './app';
import dotenv from 'dotenv';
import { db } from './config/database';

dotenv.config();

const PORT = process.env.PORT || 3000;

// Test DB connection before starting
db.pool.connect()
    .then(client => {
        console.log('Connected to Database');
        client.release();

        const server = app.listen(PORT, () => {
            console.log(`Server is running on http://localhost:${PORT}`);
        });

        server.on('request', (req: any) => {
            console.log(`Raw request: ${req.method} ${req.url}`);
        });

        server.on('error', (err: any) => {
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
