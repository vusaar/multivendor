import { categoryGuideService } from "./src/services/category.guide.service";
import { db } from "./src/config/database";

async function verify() {
    try {
        console.log("Verifying CategoryGuideService...");
        const menu = await categoryGuideService.getCategoryMenu();
        console.log(`Success! Fetched ${menu.length} categories.`);
        console.log("Sample category:", menu[0]);
        
        const snippet = await categoryGuideService.getPromptSnippet();
        console.log("Prompt snippet generated successfully.");
        // console.log(snippet);
        
        process.exit(0);
    } catch (error) {
        console.error("Verification failed:", error);
        process.exit(1);
    }
}

verify();
