const mockProductAgent = {
    name: "Agent Product",
    image: "https://store.eyamisolutions.co.zw/storage/agent_image.jpg"
};

const mockProductAPI = {
    name: "API Product",
    images: ["https://store.eyamisolutions.co.zw/storage/api_image.jpg"]
};

const mockProductNone = {
    name: "Empty Product"
};

function testLogic(product: any) {
    let imageUrl = product.image || (product.images && product.images.length > 0 ? product.images[0] : null);
    if (!imageUrl) {
        imageUrl = "https://store.eyamisolutions.co.zw/storage/placeholder.png";
    }
    return imageUrl;
}

console.log("Testing MessageProcessor Logic:");
console.log(`Agent Result: ${testLogic(mockProductAgent)}`);
console.log(`API Result:   ${testLogic(mockProductAPI)}`);
console.log(`None Result:  ${testLogic(mockProductNone)}`);

if (testLogic(mockProductAgent) === "https://store.eyamisolutions.co.zw/storage/agent_image.jpg" &&
    testLogic(mockProductAPI) === "https://store.eyamisolutions.co.zw/storage/api_image.jpg" &&
    testLogic(mockProductNone) === "https://store.eyamisolutions.co.zw/storage/placeholder.png") {
    console.log("SUCCESS: Image logic is correct!");
} else {
    console.error("FAILURE: Image logic mismatch!");
    process.exit(1);
}
