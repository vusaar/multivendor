import dotenv from 'dotenv';
dotenv.config();
console.log("GOOGLE_API_KEY set:", !!process.env.GOOGLE_API_KEY);
console.log("DB_HOST:", process.env.DB_HOST);
process.exit(0);
