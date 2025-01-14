<x-filament::page>
    <div class="space-y-4">
        <h2 class="text-xl font-bold">Webhook Info</h2>
        <pre class="p-4 bg-gray-100 rounded">
            {{ json_encode($this->getBotInfo()['webhook'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
        </pre>

        <h2 class="text-xl font-bold">GetMe Info</h2>
        <pre class="p-4 bg-gray-100 rounded">
            {{ json_encode($this->getBotInfo()['getMe'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
        </pre>

        <div class="space-y-2">
            <a href="{{ $this->getWebHookUrl() }}" class="block px-4 py-2 text-center text-white bg-blue-600 rounded hover:bg-blue-700">
                SetWebHook
            </a>
            <a href="{{ $this->getGetMeUrl() }}" class="block px-4 py-2 text-center text-white bg-green-600 rounded hover:bg-green-700">
                GetMe
            </a>
        </div>
    </div>
</x-filament::page>
