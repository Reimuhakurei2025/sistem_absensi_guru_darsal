@php
    $messages = [
        'success' => ['class' => 'bg-green-50 text-green-800 border-green-200', 'icon' => 'M5 13l4 4L19 7'],
        'error'   => ['class' => 'bg-red-50 text-red-800 border-red-200',       'icon' => 'M6 18L18 6M6 6l12 12'],
        'info'    => ['class' => 'bg-blue-50 text-blue-800 border-blue-200',    'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['class' => 'bg-yellow-50 text-yellow-800 border-yellow-200','icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ];
@endphp

@foreach($messages as $type => $config)
    @if(session($type))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 6000)"
             class="flex items-start gap-3 p-4 mb-3 rounded-lg border {{ $config['class'] }}">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
            </svg>
            <div class="flex-1 text-sm font-medium">
                {{ session($type) }}
            </div>
            <button @click="show = false" class="flex-shrink-0 hover:opacity-70" type="button" aria-label="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif
@endforeach

{{-- Validation errors --}}
@if($errors->any())
    <div class="p-4 mb-3 rounded-lg border bg-red-50 text-red-800 border-red-200">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1 text-sm">
                <strong class="font-semibold">Mohon perbaiki kesalahan berikut:</strong>
                <ul class="mt-1 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
