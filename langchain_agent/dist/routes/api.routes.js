"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const search_controller_1 = require("../controllers/search.controller");
const whatsapp_controller_1 = require("../controllers/whatsapp.controller");
const embeddings_controller_1 = require("../controllers/embeddings.controller");
const router = (0, express_1.Router)();
router.post('/search', search_controller_1.searchController);
router.post('/embeddings/generate', embeddings_controller_1.generateEmbeddingController);
router.post('/embeddings/format-and-generate', embeddings_controller_1.formatAndGenerateEmbeddingController);
// WhatsApp Webhook
router.get('/whatsapp/webhook', whatsapp_controller_1.whatsappController.verifyWebhook);
router.post('/whatsapp/webhook', whatsapp_controller_1.whatsappController.handleWebhook);
exports.default = router;
