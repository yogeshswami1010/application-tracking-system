@props([
    'id' => null,
    'size' => 'md', // sm, md, lg, xl, full
    'title' => null,
    'footer' => true,
    'closeButton' => true,
])

@php
    $modalId = $id ?? 'modal-' . uniqid();
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-2xl',
        'lg' => 'max-w-4xl',
        'xl' => 'max-w-6xl',
        'full' => 'max-w-full mx-4',
    ];
@endphp

<div 
    id="{{ $modalId }}"
    class="hidden fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-labelledby="modal-title-{{ $modalId }}"
    aria-hidden="true"
    {{ $attributes->except(['class']) }}
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div 
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
        ></div>
        
        <!-- Modal -->
        <div class="relative bg-white rounded-lg shadow-xl {{ $sizeClasses[$size] }} w-full max-h-[90vh] overflow-hidden">
            <!-- Header -->
            @if($title || $closeButton)
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    @if($title)
                        <h3 class="text-lg font-semibold text-gray-900 uppercase" id="modal-title-{{ $modalId }}">
                            {{ $title }}
                        </h3>
                    @endif
                    @if($closeButton)
                        <button 
                            type="button" 
                            class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                            onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                        >
                            <i class="fa fa-times"></i>
                        </button>
                    @endif
                </div>
            @endif
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            @if($footer)
                <div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $footer ?? '' }}
                </div>
            @endif
        </div>
    </div>
</div>

