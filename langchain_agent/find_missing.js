const fs = require('fs');
const path = require('path');

const dbData = JSON.parse(fs.readFileSync('db_data.json', 'utf8'));
const existingImages = fs.readdirSync('C:\\xampp4\\htdocs\\multistore\\storage\\app\\public\\product_images');

const missing = [];

const productsMap = {};
dbData.products.forEach(p => {
    productsMap[p.id] = p;
});

dbData.images.forEach(img => {
    const filename = path.basename(img.image);
    if (!existingImages.includes(filename)) {
        const product = productsMap[img.product_id];
        if (product) {
            missing.push({
                product_id: img.product_id,
                filename: filename,
                name: product.name,
                description: product.description
            });
        }
    }
});

fs.writeFileSync('missing_images_report.json', JSON.stringify(missing, null, 2));
console.log(`Found ${missing.length} missing images.`);
