@props(['status' => 'high_growth', 'rate' => 0])

@php
    $badgeClass = match($status) {
        'high_growth'   => 'growth-badge-bullish',
        'steady_growth' => 'growth-badge-steady',
        'stable'        => 'growth-badge-stable',
        default         => 'growth-badge-declining',
    };
    $icon = match($status) {
        'high_growth'   => '🚀',
        'steady_growth' => '📈',
        'stable'        => '⚖️',
        default         => '🔻',
    };
@endphp

<span class="{{ $badgeClass }}">
    <span>{{ $icon }}</span>
    <span>{{ $rate >= 0 ? '+' : '' }}{{ number_format($rate, 1) }}% MoM</span>
</span>
