<aside id="sidebar" class="md:fixed md:inset-y-0 md:w-64 md:flex md:flex-col bg-white border-r border-gray-200 z-30">
    <!-- Mobile menu button -->
    <div class="md:hidden p-2">
        <button type="button" id="sidebarOpenBtn" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
            <!-- Heroicon: Menu -->
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    <!-- Sidebar for desktop -->
    <nav class="mt-5 flex-1 space-y-1 px-2">
        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Dashboard</a>
        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Products</a>
        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Orders</a>
        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Vendors</a>
        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Categories</a>
    </nav>
    <!-- Mobile sidebar overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 z-40 flex md:hidden hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" id="sidebarOverlayBg"></div>
        <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-5 pb-4">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button type="button" id="sidebarCloseBtn" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <!-- Heroicon: X -->
                    <svg class="h-6 w-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="mt-5 flex-1 space-y-1 px-2">
                <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Dashboard</a>
                <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Products</a>
                <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Orders</a>
                <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Vendors</a>
                <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900">Categories</a>
            </nav>
        </div>
        <div class="w-14 flex-shrink-0" aria-hidden="true"></div>
    </div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
    var sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var sidebarOverlayBg = document.getElementById('sidebarOverlayBg');

    if (sidebarOpenBtn && sidebarOverlay) {
        sidebarOpenBtn.addEventListener('click', function () {
            sidebarOverlay.classList.remove('hidden');
        });
    }
    if (sidebarCloseBtn && sidebarOverlay) {
        sidebarCloseBtn.addEventListener('click', function () {
            sidebarOverlay.classList.add('hidden');
        });
    }
    if (sidebarOverlayBg && sidebarOverlay) {
        sidebarOverlayBg.addEventListener('click', function () {
            sidebarOverlay.classList.add('hidden');
        });
    }
});
</script>
