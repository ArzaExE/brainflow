@props(['user' => null, 'name' => null, 'size' => 'md', 'style' => ''])

@php
$displayName = $user['name'] ?? $user->name ?? $name ?? '?';
$initials = collect(explode(' ', $displayName))
    ->map(fn($p) => strtoupper(mb_substr($p, 0, 1)))
    ->take(2)
    ->implode('');

$colors = [
    '#6366f1','#8b5cf6','#ec4899','#f43f5e',
    '#f97316','#22c55e','#14b8a6','#3b82f6',
    '#06b6d4','#a855f7','#d946ef','#84cc16',
];
$hash = array_sum(array_map('ord', str_split($displayName)));
$bg   = $colors[$hash % count($colors)];

$sizeClass = match($size) {
    'sm' => 'avatar--sm',
    'lg' => 'avatar--lg',
    default => 'avatar--md',
};
@endphp

<span
    class="avatar {{ $sizeClass }}"
    style="background:{{ $bg }};{{ $style }}"
    title="{{ $displayName }}"
>{{ $initials }}</span>
