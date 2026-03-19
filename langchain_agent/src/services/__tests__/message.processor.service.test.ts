import { messageProcessorService } from '../message.processor.service';
import { whatsappService } from '../whatsapp.service';
import { sessionService } from '../session.service';
import { productSearchService } from '../product.search.service';

// Mock dependencies
jest.mock('../whatsapp.service');
jest.mock('../session.service');
jest.mock('../product.search.service');

describe('MessageProcessorService', () => {
    const from = '1234567890';

    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('should handle a new search message', async () => {
        const msgBody = 'blue shoes';
        const mockSession = { lastQuery: null, currentPage: 1 };
        const mockSearchData = {
            data: [{ id: 1, name: 'Blue Shoe', images: [] }],
            meta: { current_page: 1, last_page: 1 }
        };

        (sessionService.getSession as jest.Mock).mockResolvedValue(mockSession);
        (productSearchService.search as jest.Mock).mockResolvedValue(mockSearchData);
        (whatsappService.formatProductCaption as jest.Mock).mockReturnValue('Caption');
        (whatsappService.uploadMedia as jest.Mock).mockResolvedValue('media-id');

        await messageProcessorService.handleIncomingMessage(from, msgBody, null);

        expect(sessionService.updateSession).toHaveBeenCalledWith(from, { lastQuery: msgBody, currentPage: 1 });
        expect(productSearchService.search).toHaveBeenCalledWith(msgBody, 1, from);
        expect(whatsappService.uploadMedia).toHaveBeenCalled();
        expect(whatsappService.sendImageById).toHaveBeenCalledWith(from, 'media-id', 'Caption');
    });

    it('should handle pagination via button reply', async () => {
        const interactive = {
            type: 'button_reply',
            button_reply: { id: 'next_page' }
        };
        const mockSession = { lastQuery: 'blue shoes', currentPage: 1 };
        const mockSearchData = {
            data: [{ id: 2, name: 'Blue Shoe 2', images: [] }],
            meta: { current_page: 2, last_page: 2 }
        };

        (sessionService.getSession as jest.Mock).mockResolvedValue(mockSession);
        (productSearchService.search as jest.Mock).mockResolvedValue(mockSearchData);
        (whatsappService.formatProductCaption as jest.Mock).mockReturnValue('Caption 2');

        await messageProcessorService.handleIncomingMessage(from, null, interactive);

        expect(productSearchService.search).toHaveBeenCalledWith('blue shoes', 2, from);
        expect(sessionService.updateSession).toHaveBeenCalledWith(from, { currentPage: 2 });
    });
});
