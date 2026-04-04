import { Router } from 'express';
import { searchController } from '../controllers/search.controller';
import { whatsappController } from '../controllers/whatsapp.controller';
import { syncCategoryController, suggestSynonymsController } from '../controllers/category.controller';
import { generateEmbeddingController, formatAndGenerateEmbeddingController } from '../controllers/embeddings.controller';

const router = Router();

router.post('/search', searchController);
router.post('/embeddings/generate', generateEmbeddingController);
router.post('/embeddings/format-and-generate', formatAndGenerateEmbeddingController);

// Category Sync & Suggestions
router.post('/categories/sync', syncCategoryController);
router.post('/categories/suggest-synonyms', suggestSynonymsController);

// WhatsApp Webhook
router.get('/whatsapp/webhook', whatsappController.verifyWebhook);
router.post('/whatsapp/webhook', whatsappController.handleWebhook);

export default router;
