@props([
    'label' => 'Statistik',
    'value' => 0,
    'icon'  => 'users',
    'color' => 'primary', // primary, green, yellow, red, blue
    'subtitle' => null,
])

@php
    $colors = [
        'primary' => 'bg-primary-50 text-primary-700',
        'green'   => 'bg-green-50 text-green-700',
        'yellow'  => 'bg-yellow-50 text-yellow-700',
        'red'     => 'bg-red-50 text-red-700',
        'blue'    => 'bg-blue-50 text-blue-700',
        'gray'    => 'bg-gray-50 text-gray-700',
    ];
    $iconBg = $colors[$color] ?? $colors['primary'];
@endphp

<div class="card flex items-start gap-4">
    <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 {{ $iconBg }}">
        <x-sidebar-icon :name="$icon" class="w-6 h-6" />
    </div>
    <div class="flex-1 min-w-0">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">
            {{ $label }}
        </div>
        <div class="text-2xl font-bold text-gray-900 mt-1">
            {{ $value }}
        </div>
        @if($subtitle)
            <div class="text-xs text-gray-500 mt-1">{{ $subtitle }}</div>
        @endif
    </div>
</div>
