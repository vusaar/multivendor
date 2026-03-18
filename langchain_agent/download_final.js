const fs = require('fs');
const path = require('path');
const https = require('https');

const TARGET_DIR = 'C:\\xampp4\\htdocs\\multistore\\storage\\app\\public\\product_images';

const downloads = [
    { url: 'https://cdn.braun-hamburg.com/media/catalog/product/cache/c7b500f5dd69dfcc3849f418a469152a/6/3/6300_065359_290_sonrisa-casual-hemd_001_p.1755081926.jpg', target: 'ek04FfVbg4Cwx6A0VT4M0sX2kTeUl7VvdI3q9xIR.png' },
    { url: 'https://static.vecteezy.com/system/resources/previews/059/046/510/non_2x/elegant-white-women-s-long-sleeve-blouse-with-puffed-shoulders-and-fitted-waist-free-png.png', target: 'kZTCy6m9GF2ZRPsdw4DhbrLuV3iMUVYrLZjvA1cp.jpg' },
    { url: 'https://www.pringleofscotland.co.za/wp-content/uploads/2025/10/PR111550-NVY-1.jpg', target: 'VOGHBt9cdRdAbHExmJBFnJR0EYkumkDn6oeyH5Mh.jpg' },
    { url: 'https://mediahub.boohooman.com/cmm22199_black_xl?qlt=70&w=549&ssz=true&dpr=1', target: 'hg5uxUcCQoZJ5EWRcCx1nwWO8wZIw6DGWsw0n3HX.jpg' }
];

async function download(url, filePath) {
    return new Promise((resolve, reject) => {
        https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
            if (res.statusCode === 200) {
                const file = fs.createWriteStream(filePath);
                res.pipe(file);
                file.on('finish', () => {
                    file.close();
                    console.log(`[SUCCESS] Downloaded to ${filePath}`);
                    resolve();
                });
            } else {
                console.error(`[ERROR] Failed to download ${url}: ${res.statusCode}`);
                reject(new Error(`Status ${res.statusCode}`));
            }
        }).on('error', (err) => {
            console.error(`[ERROR] ${err.message}`);
            reject(err);
        });
    });
}

async function run() {
    console.log('--- Final Batch Web Image Restoration ---');
    for (const item of downloads) {
        const dest = path.join(TARGET_DIR, item.target);
        try {
            await download(item.url, dest);
        } catch (e) {
            console.error(`Skipping ${item.target} due to error.`);
        }
    }
    console.log('--- Restoration Complete ---');
}

run();
