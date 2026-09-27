@props(['priority' => 'medium'])

@php
$map = [
    'high'   => ['class' => 'badge--prio-high', 'label' => 'Alta'],
    'medium' => ['class' => 'badge--prio-med',  'label' => 'Media'],
    'low'    => ['class' => 'badge--prio-low',  'label' => 'Bassa'],
];
$cfg = $map[$priority] ?? ['class' => 'badge--neutral', 'label' => $priority];
@endphp

<span class="badge {{ $cfg['class'] }}">
    <x-icon name="flag" size="sm" />
    {{ $cfg['label'] }}
</span>
