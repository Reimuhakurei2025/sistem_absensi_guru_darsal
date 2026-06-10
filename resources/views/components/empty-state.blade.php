@props([
    'icon'    => 'document',
    'title'   => 'Belum ada data',
    'message' => 'Data akan muncul di sini setelah ditambahkan.',
])

<div class="card text-center py-12">
    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
        <x-sidebar-icon :name="$icon" class="w-8 h-8 text-gray-400" />
    </div>
    <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">{{ $message }}</p>

    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
