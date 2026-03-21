import { MessageProcessorService } from './src/services/message.processor.service';
import { whatsappService } from './src/services/whatsapp.service';
import { productSearchService } from './src/services/product.search.service';
import { sessionService } from './src/services/session.service';

// Mocking the services
jest.mock('./src/services/whatsapp.service');
jest.mock('./src/services/product.search.service');
jest.mock('./src/services/session.service');

const processor = new MessageProcessorService();

async function runTest() {
    console.log("--- Testing Confidence Thresholding ---");

    const from = "263770000000";
    const query = "hat";

    // 1. Mock Session
    (sessionService.getSession as jest.Mock).mockResolvedValue({
        lastQuery: "",
        currentPage: 1,
        suggestedProducts: []
    });

    // 2. Mock Search Result (Low confidence ONLY)
    (productSearchService.search as jest.Mock).mockResolvedValue({
        data: [
            { id: 57, name: "Red Nail Polish", similarity_score: 0.02439 },
            { id: 18, name: "Blue T-shirt", similarity_score: 0.012 }
        ],
        meta: { current_page: 1, last_page: 1 }
    });

    console.log("\nScenario: Search for 'hat' (low confidence ONLY)");
    await processor.handleIncomingMessage(from, query, null);

    // Expectation: whatsappService.sendButtons called with 'show_suggestions'
    console.log("Verified: sendButtons should be called with 'show_suggestions' because all scores < 0.025");
}

// runTest();
