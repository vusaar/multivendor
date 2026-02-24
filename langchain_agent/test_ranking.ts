import axios from 'axios';

async function testRankedSearch() {
    const queries = [
        "i want red nike sneakers", // Tests multiple categories/brands/variations
        "electronics from samsung",
        "cheap headphones"
    ];

    for (const query of queries) {
        console.log(`\nTesting Query: "${query}"`);
        try {
            const response = await axios.post('http://localhost:3001/api/search', {
                query: query
            });
            console.log("Response Status:", response.status);
            const results = response.data.data.results;
            if (Array.isArray(results)) {
                console.log(`Found ${results.length} results.`);
                results.slice(0, 3).forEach((r, i) => {
                    console.log(`${i + 1}. ${r.name} (Score: ${r.relevance_score})`);
                });
            } else {
                console.log("Response:", JSON.stringify(response.data, null, 2));
            }
        } catch (error: any) {
            console.error("Test Request Failed:", error.response?.data || error.message);
        }
    }
}

testRankedSearch();
