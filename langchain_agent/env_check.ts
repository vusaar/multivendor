import dotenv from 'dotenv';
import path from 'path';

dotenv.config();

console.log('--- Environment Debug ---');
console.log('CWD:', process.cwd());
console.log('NGROK_URL:', process.env.NGROK_URL);
console.log('LARAVEL_API_URL:', process.env.LARAVEL_API_URL);
console.log('-------------------------');
