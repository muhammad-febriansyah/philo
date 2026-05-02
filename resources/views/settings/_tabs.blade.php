@php
    $settingsTabs = [
        ['route' => 'settings.general', 'label' => 'Umum', 'icon' => 'fas fa-cog'],
        ['route' => 'settings.booth', 'label' => 'Booth', 'icon' => 'fas fa-camera-retro'],
        ['route' => 'settings.payment', 'label' => 'Pembayaran', 'icon' => 'fas fa-money-bill-wave'],
        ['route' => 'settings.email', 'label' => 'Notifikasi Email', 'icon' => 'far fa-envelope'],
        ['route' => 'settings.recaptcha', 'label' => 'reCAPTCHA', 'icon' => 'fas fa-shield-alt'],
        ['route' => 'settings.about', 'label' => 'Tentang Kami', 'icon' => 'fas fa-info-circle'],
    ];
@endphp

<div class="card border-0 shadow-sm mb-4 settings-tabs-card">
    <div class="card-body p-0">
        <ul class="nav nav-pills settings-tabs flex-nowrap">
            @foreach($settingsTabs as $tab)
                @php $isActive = request()->routeIs($tab['route']); @endphp
                <li class="nav-item">
                    <a href="{{ route($tab['route']) }}"
                       class="nav-link {{ $isActive ? 'active' : '' }}">
                        <i class="{{ $tab['icon'] }} me-2"></i>
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@push('styles')
<style>
    .settings-tabs-card {
        overflow: hidden;
        border-radius: 14px !important;
    }
    .settings-tabs {
        gap: 0;
        padding: 6px;
        background: #fafaf5;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .settings-tabs::-webkit-scrollbar {
        height: 4px;
    }
    .settings-tabs::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.15);
        border-radius: 2px;
    }
    .settings-tabs .nav-item { flex-shrink: 0; }
    .settings-tabs .nav-link {
        color: #52525b;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .settings-tabs .nav-link i {
        opacity: 0.7;
        font-size: 0.82rem;
    }
    .settings-tabs .nav-link:hover {
        color: #18181b;
        background: rgba(0,0,0,0.04);
    }
    .settings-tabs .nav-link.active {
        background: #18181b;
        color: #fafaf5;
        box-shadow: 0 4px 12px rgba(24,24,27,0.18);
    }
    .settings-tabs .nav-link.active i {
        opacity: 1;
        color: #C9A800;
    }
</style>
@endpush
