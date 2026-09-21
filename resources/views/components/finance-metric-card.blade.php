@props([
    'title' => 'Metrik',
    'value' => '0',
    'subtitle' => null,
    'badgeText' => null,
    'badgeType' => 'success',
    'icon' => null,
    'iconBg' => 'primary',
])

<div class="finance-stat-card">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="overflow-hidden pe-2">
            <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">{{ $title }}</span>
            <h3 class="h2 mb-0 fw-bold mt-1 text-dark text-truncate font-monospace" style="font-size: 1.35rem; letter-spacing: -0.02em;">{{ $value }}</h3>
        </div>
        @if($icon)
            <div class="finance-stat-icon bg-{{ $iconBg }}-lt text-{{ $iconBg }} shadow-none">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if($subtitle || $badgeText)
        <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
            @if($badgeText)
                <span class="badge bg-{{ $badgeType }}-lt fw-bold">{{ $badgeText }}</span>
            @endif
            @if($subtitle)
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
</div>
