const fs = require('fs');
const path = require('path');
const https = require('https');

const TARGET_DIR = 'C:\\xampp4\\htdocs\\multistore\\storage\\app\\public\\product_images';

const downloads = [
    { url: 'https://static.nike.com/a/images/w_1280,q_auto,f_auto/no4chctl7npzsn5bhqdt/air-jordan-11-retro-legend-blue-release-date.jpg', target: 'eiI1Z78pOK6ykz1AichuXplT7Q6MTk24svpsX0aR.jpg' },
    { url: 'https://www.bigclothing4u.co.uk/media/catalog/product/cache/92a89721a3bc3cca31ca1daa4fdb902d/t/u/tumbnail_0810a3e8-c49c-4a9b-b529-afbba9f7bac6_1.jpg', target: 'TMqHZAimdwJObRcqKS55SiQGtDle2cOvtetbvZla.jpg' },
    { url: 'https://mediahub.boohooman.com/cmm12236_white_xl?qlt=70&w=549&ssz=true&dpr=1', target: 'iqEgS1Ur1FwD6qYWT3TIUac91zP3eE7PGKLQBLm1.jpg' },
    { url: 'https://thefoschini.vtexassets.com/arquivos/ids/220480132-1200-1600', target: 'Vd5MrrlxHnDDsHXacVEZgr4z4fLjinDDiNzHKDut.jpg' },
    { url: 'https://www.sosandar.com/media/catalog/product/cache/f57ffca6943f53694da5d1d8d838ac28/w/e/web_2403_165_s24tw098grfl01_16519944_1.jpg', target: 'F8O8N8iZnx9XXRlFXTox4CAPXIqe2Bo7XycwqXQp.jpg' }
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
    console.log('--- Starting Web Image Restoration ---');
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
