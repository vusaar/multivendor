const fs = require('fs');
const path = './resources/views/admin/products/edit.blade.php';
let content = fs.readFileSync(path, 'utf8');

// Update Sidebar Menu flexibly
content = content.replace(
    /<a href="#section-general"[\s\S]*?<a href="#section-variations"[\s\S]*?<\/a>/,
    `<a href="#section-media" class="pm-side-link active">\n                            <i class="cil-image"></i> Media\n                        </a>\n                        <a href="#section-general" class="pm-side-link">\n                            <i class="cil-info"></i> Basic Info\n                        </a>\n                        <a href="#section-inventory" class="pm-side-link">\n                            <i class="cil-money"></i> Pricing & Stock\n                        </a>\n                        <a href="#section-variations" class="pm-side-link">\n                            <i class="cil-layers"></i> Variations\n                        </a>`
);

// Move Media Section
const mediaRegex = /<!-- Section: Media -->\s*<section id="section-media" class="pm-card">[\s\S]*?<\/section>/;
const mediaMatch = content.match(mediaRegex);
if (mediaMatch) {
    const mediaHTML = mediaMatch[0];
    content = content.replace(mediaMatch[0], '');
    const generalRegex = /<!-- Section: General Info -->\s*<section id="section-general"/;
    content = content.replace(generalRegex, mediaHTML + "\n\n                    <!-- Section: General Info -->\n                    <section id=\"section-general\"");
}

content = content.replace(/font-size:\s*3rem;[^>]*><\/i>[\s\S]*?<\/div>[\s\S]*?<h5[^>]*>Upload new images<\/h5>[\s\S]*?<p[^>]*>Drag and drop or click to browse<\/p>/, 
`font-size: 2rem; color: var(--pm-primary);"></i>
                                </div>
                                <h6 class="fw-bold">Drag and drop images here</h6>
                                <p class="text-muted small">or click to browse from your computer</p>`);

fs.writeFileSync(path, content);
console.log("Updated edit.blade.php layout successfully.");
