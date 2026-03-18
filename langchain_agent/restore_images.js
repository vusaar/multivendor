const fs = require('fs');
const path = require('path');

const BACKUP_DIR = 'C:\\Users\\Admin\\Pictures\\eyami samples';
const TARGET_DIR = 'C:\\xampp4\\htdocs\\multistore\\storage\\app\\public\\product_images';

// Ensure target directory exists
if (!fs.existsSync(TARGET_DIR)) {
    fs.mkdirSync(TARGET_DIR, { recursive: true });
}

const mapping = [
    // Root level matches
    { backup: 'adidas campus black.jpg', target: 'tJhRWG7GIJK3iJAX5mEdu8xFUNtyYiww7uGKOZdC.jpg' },
    { backup: 'jordan  3.jpg', target: '22JUB5PKq8vMMDqehWLiS8n3tp0gZccNbGv0CdAR.jpg' },
    { backup: 'jordan 31.jpg', target: 'ZcRidkX5cDFfUmSv6QgqnKm7JQXM23gCmGfmvFdJ.jpg' },
    { backup: 'adidas sneaker.jpg', target: 'd4PoevpguFXLK8o4TGwy219VZUfXbXJiIFNcuJAE.jpg' },
    { backup: 'jordan 1 red.jpg', target: 'IVQQhE8pp8mHOR18hnznptAu6llZt7C1RUHoBrzF.jpg' },
    { backup: 'adidas prophere.jpg', target: '3rsw4tRr8oT4kIbxbHsHN9DbZMXNrKfV2GGdUBv2.jpg' },
    { backup: 'adidas zx750.jpg', target: 'zz9x6wUGVqsbPDnXTv8Pntc8f4hplXPihsSqojBz.jpg' },
    { backup: 'long sleeve casual shirt.jpg', target: 'OXWmkd1A3Q1ibvRGHS22Q8najnB1E5Tffr3hF0gN.jpg' },
    { backup: 'short sleeve shirt.jpg', target: 'kPjyc54gQG8SjkvI3gNinNjLtQwSjtbMwqReuN7X.png' },
    { backup: 'regular fit long sleeve shirt.jpg', target: 'tKwhPfzLj7U1aeeWFwByVMFviR7oes8xApFWitdl.jpg' },
    { backup: 'strippled casual shirt.jpg', target: 'qraUIzzlRVmSIwCiWYmWQbEAnpr2sBLh3C2v33zC.jpg' },
    { backup: 'short sleeve shirt.jpg', target: 'jYMIRdJXond8a7lepYLOJ5qYCixSK2KVrbLaij89.jpg' },
    { backup: 't.png', target: 'VJOV9nyXlfBbGIvBKtlTsLt6OuF2UZsfIvIiozvr.png' },
    { backup: '0.png', target: 'zvkqWYRvmtreoOrlMpzUgu3tKyleRAQi1WsCodHD.png' },

    // Subfolder: mens
    { backup: 'mens/ben sherman.jpg', target: 'Z610lb44SrvUllkT3wbkROSXLMplhpiKf5EFFFMM.jpg' },
    { backup: 'mens/ben sherman 2.jpg', target: 'Nl9sFfT1jyevFHOznfn1YdkZg3vBV1hZOQBTZHDl.jpg' },
    { backup: 'mens/boohooman shortlseeve.jpg', target: 'yUyHCdAMiTJtN2gTaXvWjO4CD474E2XuANojvKD8.jpg' },
    { backup: 'mens/boohooman.jpg', target: 'FJgtF4Aon6nBC77PONrx8FkoNiiNdkaa7X3ZA7r5.jpg' },
    { backup: 'mens/mango -mandarian collar.jpg', target: 'MPDpyMMpwa9s4kfl86eJkdNyBPwuEFf0Tx6eaG6w.jpg' },
    { backup: 'mens/polo 2.jpg', target: 'gi4cD5LqqSCpnIV0zQgOXI9FEfKFW2MeaMnFO4Rr.jpg' },
    { backup: 'mens/pringle of scotland.jpg', target: 'x7cAZaNj1blnpMsha14fHBCbeD8VePHSOAY7DXIW.jpg' },
    { backup: 'mens/polo.jpg', target: 'sjDE0ki79wse3NTpou9QsPAv73ugdpB1eUlFV5W3.jpg' },

    // Subfolder: women
    { backup: 'women/denim real-denim-shirt.jpg', target: 'ZswpDybcOrXmTG0m6e0qSgXwsUqYnGPXd1JzkWYG.jpg' },
    { backup: 'women/greenn real-blouse.jpg', target: 'qVa4IuYKcC6fs7T8OZPjPKRh50HtABiTvavZNzq1.jpg' },
    { backup: 'women/whited black doted real-printed-blouse.jpg', target: 'HMd1vhzi3OE5xd3kFq4f75KLZsX485MW2iHMrcc6.jpg' },
    { backup: 'women/blouse black  real-popover-blouse.jpg', target: 'YxBW3DeMusFm4XPLp0jYR8lWfVImwIMHpEqzRE0S.jpg' },
    { backup: 'women/floral fresh-slub-shirt.jpg', target: 'diA0tLvG4W387vEKwLOiYd829T0wZ1tfbDCvN9jY.jpg' }
];

console.log('--- Starting Expanded Image Restoration ---');

mapping.forEach(item => {
    // Normalize path separators for Windows
    const backupSubPath = item.backup.replace(/\//g, path.sep);
    const sourcePath = path.join(BACKUP_DIR, backupSubPath);
    const destPath = path.join(TARGET_DIR, item.target);

    if (fs.existsSync(sourcePath)) {
        try {
            fs.copyFileSync(sourcePath, destPath);
            console.log(`[SUCCESS] Copied "${item.backup}" to "${item.target}"`);
        } catch (err) {
            console.error(`[ERROR] Failed to copy "${item.backup}":`, err.message);
        }
    } else {
        console.warn(`[MISSING] Backup file not found: "${sourcePath}"`);
    }
});

console.log('--- Restoration Complete ---');
