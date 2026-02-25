import { Router } from 'express';
import { searchController } from '../controllers/search.controller';
import { whatsappController } from '../controllers/whatsapp.controller';

const router = Router();

router.post('/search', searchController);

// WhatsApp Webhook
router.get('/whatsapp/webhook', whatsappController.verifyWebhook);
router.post('/whatsapp/webhook', whatsappController.handleWebhook);

export default router;
