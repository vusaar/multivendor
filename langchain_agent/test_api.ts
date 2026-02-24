import axios from 'axios';

async function testSearch() {
    try {
        const response = await axios.post('http://localhost:3001/api/search', {
            query: "show me some active products with their images"
        });
        console.log("Response Status:", response.status);
        console.log("Response Data:", JSON.stringify(response.data, null, 2));
    } catch (error: any) {
        console.error("Test Request Failed:", error.response?.data || error.message);
    }
}

testSearch();
