 <!-- Floating Cart Button -->
 @php
     $cartItemCount = 3; // Example cart item count
 @endphp
 @if ($cartItemCount > 0)
     <div class="fixed bottom-20 right-4 z-10">
         <div class="relative">
             <button class="bg-purple-600 text-white p-3 rounded-full shadow-lg">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                 </svg>
             </button>
             <span
                 class="absolute -top-1 -right-1 bg-gray-800 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                 {{ $cartItemCount }}
             </span>
         </div>
     </div>
 @endif
