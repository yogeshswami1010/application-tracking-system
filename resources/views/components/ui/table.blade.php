@props([
    'striped' => false,
    'hover' => true,
    'bordered' => false,
    'responsive' => false,
])

@php
    $tableClasses = 'min-w-full divide-y divide-gray-200';
    
    if ($bordered) {
        $tableClasses .= ' border border-gray-200';
    }
@endphp

@if($responsive)
    <div class="overflow-x-auto -mx-4 sm:mx-0">
        <div class="inline-block min-w-full align-middle">
@endif

<table {{ $attributes->merge(['class' => $tableClasses]) }}>
    <thead class="bg-gray-50">
        {{ $thead ?? '' }}
    </thead>
    <tbody class="bg-white divide-y divide-gray-200 {{ $striped ? 'divide-y divide-gray-200' : '' }} {{ $hover ? '[&>tr]:hover:bg-gray-50' : '' }}">
        {{ $slot }}
    </tbody>
    @if(isset($tfoot))
        <tfoot class="bg-gray-50">
            {{ $tfoot }}
        </tfoot>
    @endif
</table>

@if($responsive)
        </div>
    </div>
@endif

