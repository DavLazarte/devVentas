@props(['categories' => []])

<div class="py-4 px-4">
    <div class="flex overflow-x-auto space-x-4 pb-2 hide-scrollbar">
        @foreach ($categories as $category)
            <a href="{{ route('all-shops', ['category' => $category['id']]) }}"
               class="flex flex-col items-center space-y-1 min-w-[60px] group">
                <div class="w-14 h-14 rounded-full border-2 border-gray-200 flex items-center justify-center bg-white group-hover:border-purple-500 transition-colors">
                    <div class="text-purple-600 group-hover:text-purple-700 transition-colors">
                        {!! $category['icon'] !!}
                    </div>
                </div>
                <span class="text-xs text-center text-gray-600 group-hover:text-purple-600 transition-colors">
                    {{ $category['name'] }}
                </span>
            </a>
        @endforeach
    </div>
    {{-- <div class="flex justify-center mt-2 space-x-1">
    @foreach ($categories as $index => $category)
        <div class="h-1.5 w-1.5 rounded-full {{ $index === 0 ? 'bg-purple-600' : 'bg-gray-300' }}"></div>
    @endforeach
</div> --}}
</div>

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
