<div class="space-y-4">
    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $productName }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $productUrl }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Live Preview
            </span>
        </div>
    </div>

    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" style="height: 70vh;">
        <iframe src="{{ $productUrl }}" class="w-full h-full" frameborder="0"
            sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"
            loading="lazy" title="Product Preview - {{ $productName }}"></iframe>
    </div>

    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
        This is a live preview of how your product appears on the website.
        Some interactive features may be limited in preview mode.
    </div>
</div>
