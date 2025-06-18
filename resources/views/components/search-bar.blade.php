@props([
    'placeholder' => 'Buscar...',
    'model' => 'search',
    'debounce' => 500
])

<div class="relative">
    <input
        type="text"
        wire:model.live.debounce.{{ $debounce }}ms="{{ $model }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'w-full py-2 pl-4 pr-10 rounded-md border border-gray-300 focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500']) }}
    >
    <div wire:loading wire:target="{{ $model }}" class="absolute right-3 top-2.5">
        <svg class="animate-spin h-5 w-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <div wire:loading.remove wire:target="{{ $model }}" class="absolute right-3 top-2.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>
</div>
