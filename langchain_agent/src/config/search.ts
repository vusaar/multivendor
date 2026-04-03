/**
 * Global Search Configuration
 * Centralizing thresholds for consistency across the Agent and Message Processor.
 */
export const SEARCH_CONFIG = {
    // Score above which a product is considered a high-confidence match
    THRESHOLD_VERIFIED: 180.0, // Literal match or strong synonym/entity match
    
    // Score above which a product is considered relevant suggestion
    THRESHOLD_SUGGESTION: 40.0,
    
    // Stricter threshold for suggestions when a verified match is already present
    THRESHOLD_PRECISION_LIMIT: 80.0,
    
    // Max verified products to show in carousel
    LIMIT_VERIFIED: 3,
    
    // Max suggested products to show in carousel
    LIMIT_SUGGESTIONS: 3
};
