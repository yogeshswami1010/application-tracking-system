@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'label' => null,
    'help' => null,
    'size' => 'md', // sm, md, lg
    'options' => [],
])

@php
    $selectId = $id ?? $name;
    $baseClasses = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-3 text-base',
    ];
    
    $classes = $baseClasses . ' ' . $sizeClasses[$size];
    
    if ($error || ($attributes->has('class') && str_contains($attributes->get('class'), 'is-invalid'))) {
        $classes .= ' border-red-500 focus:border-red-500 focus:ring-red-500';
    }
    
    if ($disabled) {
        $classes .= ' bg-gray-100 cursor-not-allowed';
    }
@endphp

@if($label)
    <label for="{{ $selectId }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
@endif

<select 
    @if($name) name="{{ $name }}" @endif
    @if($selectId) id="{{ $selectId }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
    @if(count($options) > 0)
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @if($value == $optionValue) selected @endif>
                {{ $optionLabel }}
            </option>
        @endforeach
    @endif
</select>

@if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif

@if($help && !$error)
    <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
@endif

