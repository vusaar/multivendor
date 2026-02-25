import { db } from '../config/database';

export class SessionService {
    async getSession(userId: string) {
        try {
            const res = await db.query(
                'SELECT data FROM whatsapp_sessions WHERE phone_number = $1',
                [userId]
            );

            if (res.rows.length === 0) {
                const initialData = { history: [], lastActive: Date.now() };
                await db.query(
                    'INSERT INTO whatsapp_sessions (phone_number, data) VALUES ($1, $2)',
                    [userId, JSON.stringify(initialData)]
                );
                return initialData;
            }

            return res.rows[0].data;
        } catch (error) {
            console.error('[SESSION] Error getting session:', error);
            return { history: [], lastActive: Date.now() };
        }
    }

    async updateSession(userId: string, data: any) {
        try {
            const currentSession = await this.getSession(userId);
            const updatedData = { ...currentSession, ...data, lastActive: Date.now() };

            await db.query(
                'UPDATE whatsapp_sessions SET data = $2, updated_at = CURRENT_TIMESTAMP WHERE phone_number = $1',
                [userId, JSON.stringify(updatedData)]
            );
        } catch (error) {
            console.error('[SESSION] Error updating session:', error);
        }
    }

    async clearSession(userId: string) {
        try {
            await db.query('DELETE FROM whatsapp_sessions WHERE phone_number = $1', [userId]);
        } catch (error) {
            console.error('[SESSION] Error clearing session:', error);
        }
    }
}

export const sessionService = new SessionService();
