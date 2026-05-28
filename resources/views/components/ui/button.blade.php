@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info
    'size' => 'md', // xs, sm, md, lg
    'outline' => false,
    'block' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $variantClasses = [
        'primary' => $outline 
            ? 'border border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500' 
            : 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => $outline 
            ? 'border border-gray-600 text-gray-600 hover:bg-gray-50 focus:ring-gray-500' 
            : 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
        'success' => $outline 
            ? 'border border-green-600 text-green-600 hover:bg-green-50 focus:ring-green-500' 
            : 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'danger' => $outline 
            ? 'border border-red-600 text-red-600 hover:bg-red-50 focus:ring-red-500' 
            : 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'warning' => $outline 
            ? 'border border-yellow-600 text-yellow-600 hover:bg-yellow-50 focus:ring-yellow-500' 
            : 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
        'info' => $outline 
            ? 'border border-cyan-600 text-cyan-600 hover:bg-cyan-50 focus:ring-cyan-500' 
            : 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500',
    ];
    
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size];
    
    if ($block) {
        $classes .= ' w-full';
    }
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ml-2"></i>
    @endif
</button>

