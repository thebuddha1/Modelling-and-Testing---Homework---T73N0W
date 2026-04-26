@props(['value', 'index'])

@php
    $full = floor($value);
    $fraction = $value - $full;
@endphp

@if ($index <= $full)
    {{-- Full circle --}}
    <span class="relative h-3 w-3 rounded-full bg-blue-500 block"></span>

@elseif ($index == $full + 1 && $fraction > 0)
    {{-- Partial circle --}}
    @php $percent = intval($fraction * 100); @endphp
    <span class="relative h-3 w-3 rounded-full bg-gray-200 overflow-hidden block">
        <span class="absolute left-0 top-0 h-full bg-blue-500" style="width: {{ $percent }}%;"></span>
    </span>

@else
    {{-- Empty circle --}}
    <span class="relative h-3 w-3 rounded-full bg-gray-200 block"></span>
@endif

