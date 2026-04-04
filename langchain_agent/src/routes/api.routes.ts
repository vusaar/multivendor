import { Router } from 'express';
import { searchController } from '../controllers/search.controller';
import { whatsappController } from '../controllers/whatsapp.controller';
import { syncCategoryController } from '../controllers/category.controller';
import { generateEmbeddingController, formatAndGenerateEmbeddingController } from '../controllers/embeddings.controller';

const router = Router();

router.post('/search', searchController);
router.post('/embeddings/generate', generateEmbeddingController);
router.post('/embeddings/format-and-generate', formatAndGenerateEmbeddingController);

// Category Sync
router.post('/categories/sync', syncCategoryController);

// WhatsApp Webhook
router.get('/whatsapp/webhook', whatsappController.verifyWebhook);
router.post('/whatsapp/webhook', whatsappController.handleWebhook);

export default router;
