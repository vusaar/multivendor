import { db } from './src/config/database';
import * as fs from 'fs';
import * as path from 'path';
import dotenv from 'dotenv';

dotenv.config();

async function generate() {
    const res = await db.query('SELECT p.id, p.name, p.price, p.search_context, c.name as cat FROM products p JOIN categories c ON p.category_id = c.id');
    const products: any[] = res.rows;
    
    const testCases: any[] = [];
    
    products.forEach((p: any) => {
        const id = parseInt(p.id);
        const name = p.name;
        const cat = p.cat;
        const ctx = p.search_context || '';
        
        // Basic match (Name)
        testCases.push({
            query: name,
            description: `Exact Name Match for ID ${id}`,
            expected: { must_contain: [id], min_confidence: 100 }
        });
        
        // Realistic variation (Context-based)
        let realisticQuery = name;
        const brandMatch = ctx.match(/Brand: ([^|]*)/);
        const colorMatch = ctx.match(/Attributes: ([^|]*)/);
        
        if (brandMatch && !name.toLowerCase().includes(brandMatch[1].toLowerCase().trim())) {
            realisticQuery = brandMatch[1].trim() + " " + realisticQuery;
        }
        
        if (colorMatch) {
            const colors = colorMatch[1].split(',').map((s: string) => s.trim().split(' ')[0]); // Get first word of each attribute (usually color)
            if (colors[0] && !name.toLowerCase().includes(colors[0].toLowerCase())) {
                realisticQuery = colors[0] + " " + realisticQuery;
            }
        }
        
        if (realisticQuery !== name) {
            testCases.push({
                query: realisticQuery,
                description: `Real-world Multi-attribute for ID ${id}`,
                expected: { must_contain: [id], min_confidence: 80 }
            });
        }
    });
    
    // Add User's specific cases
    testCases.push({ query: "ladies tshirts", description: "Synonym (ladies) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "mens tshirts", description: "Synonym (mens) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "woman tshirts", description: "Synonym (woman) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "gents tshirts", description: "Synonym (gents) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "v-neck tshirts", description: "Attribute (v-neck) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "round neck tshirt", description: "Attribute (round neck) + Product Type", expected: { min_confidence: 50 }});
    testCases.push({ query: "golf tshirts", description: "Sub-type / Style (golf)", expected: { min_confidence: 30 }});
    
    // Noise Rejection
    testCases.push({ query: "Pizza", description: "No results expected", expected: { max_results: 0 }});
    testCases.push({ query: "Electric drill", description: "No results expected", expected: { max_results: 0 }});
    testCases.push({ query: "Burger", description: "No results expected", expected: { max_results: 0 }});

    const outputPath = path.join(__dirname, 'test', 'full_catalog_validation.json');
    fs.writeFileSync(outputPath, JSON.stringify(testCases, null, 2));
    console.log(`Generated ${testCases.length} test cases to ${outputPath}`);
    process.exit(0);
}

generate();
