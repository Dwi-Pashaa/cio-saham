<header class="navbar navbar-expand-md enterprise-top-header sticky-top d-print-none">
    <div class="container-xl">
        <!-- =================================================================
             1. MOBILE NATIVE APP BAR (Visible on Mobile Only: < 768px)
             ================================================================= -->
        <div class="d-flex d-md-none align-items-center justify-content-between w-100 py-1">
            <!-- Left: User Greeting & Avatar -->
            <div class="d-flex align-items-center gap-2">
                @if(Auth::check())
                    <div class="avatar-ring-wrap" style="padding: 1.5px;">
                        <span class="enterprise-avatar" style="width: 34px; height: 34px; font-size: 0.8rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="d-flex flex-column" style="line-height: 1.15;">
                        <div class="fw-bold text-dark fs-4">
                            Halo, {{ explode(' ', Auth::user()->name)[0] }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 1px;"><path d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" /></svg>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-0.5" style="font-size: 0.7rem; color: #16a34a; font-weight: 600;">
                            <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span>
                            <span>{{ !Auth::user()->can('view-shareholders') ? 'Investor Saham Aktif' : 'CIO Saham Live' }}</span>
                        </div>
                    </div>
                @else
                    <div class="brand-logo-frame" style="height: 32px; padding: 2px 6px;">
                        <img src="{{ asset('img/logo.jpg') }}" alt="Logo" class="brand-logo-img" style="height: 22px;">
                    </div>
                    <span class="brand-name-bold" style="font-size: 0.95rem;">CIO SAHAM</span>
                @endif
            </div>

            <!-- Right: Quick Action Controls (Refresh & User Menu) -->
            <div class="d-flex align-items-center gap-2">
                <!-- Quick Refresh Button -->
                <button type="button" class="android-icon-btn" onclick="if(window.refreshFinanceChart) { window.refreshFinanceChart(); } else { window.location.reload(); }" title="Segarkan Data">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                </button>

                <!-- Profile Menu Dropdown Trigger -->
                @if(Auth::check())
                    <div class="nav-item dropdown">
                        <a href="#" class="android-icon-btn text-decoration-none" data-bs-toggle="dropdown" aria-label="Menu Pengguna">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 enterprise-user-dropdown">
                            <div class="dropdown-header px-3 py-2.5 rounded-top" style="background-color: var(--saham-surface-subtle); border-bottom: 1px solid var(--saham-border-blue);">
                                <div class="fw-bold text-dark fs-4">{{ Auth::user()->name }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;">{{ Auth::user()->email }}</div>
                                <span class="badge bg-blue-lt fw-bold mt-1.5">{{ Auth::user()->roles->first()->name ?? 'Administrator' }}</span>
                            </div>
                            <div class="dropdown-divider my-1"></div>
                            <a href="{{ route('profile') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2">
                                <span class="dropdown-item-icon bg-primary-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark">Profil Saya</div>
                                    <div class="small text-muted">Informasi akun & keamanan</div>
                                </div>
                            </a>
                            @if(Auth::user()->can('manage-settings') || Auth::user()->can('view-settings'))
                                <a href="{{ route('setting') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2">
                                    <span class="dropdown-item-icon bg-blue-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">Pengaturan Sistem</div>
                                        <div class="small text-muted">Kredensial API & preferensi</div>
                                    </div>
                                </a>
                            @endif
                            <a href="{{ route('logout') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2 text-danger">
                                <span class="dropdown-item-icon bg-danger-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                                </span>
                                <div>
                                    <div class="fw-semibold text-danger">Keluar (Logout)</div>
                                    <div class="small text-muted">Akhiri sesi akun Anda</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- =================================================================
             2. DESKTOP ENTERPRISE BRAND & LOCKUP (Visible on >= 768px Only)
             ================================================================= -->
        <div class="d-none d-md-flex align-items-center justify-content-between w-100">
            <!-- Brand Logo & Identity Lockup -->
            <div class="navbar-brand pe-0 pe-md-4 m-0">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none enterprise-brand-link">
                    <div class="brand-logo-frame">
                        <img src="{{ asset('img/logo.jpg') }}" alt="{{ config('app.name', 'CIO Saham') }}" class="brand-logo-img">
                    </div>
                    <div class="brand-text-block">
                        <div class="d-flex align-items-center gap-1.5 brand-title-row">
                            <span class="brand-name-bold">CIO SAHAM</span>
                            <span class="brand-portal-tag">PORTAL</span>
                        </div>
                        <span class="brand-tagline">Ekuitas & Manajemen Investor</span>
                    </div>
                </a>
            </div>

            <!-- Right Side Controls: Status Indicator & User Menu -->
            <div class="navbar-nav flex-row align-items-center gap-3 ms-auto">
                <!-- Real-time Live Connection Pill -->
                <div class="d-flex align-items-center gap-2 enterprise-status-pill">
                    <span class="pulse-live-dot"></span>
                    <span class="status-pill-text">CIO Network Live</span>
                </div>

                <!-- User Profile Dropdown -->
                @if(Auth::check())
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link enterprise-user-btn" data-bs-toggle="dropdown" aria-label="Menu Pengguna" aria-expanded="false">
                            <div class="avatar-ring-wrap">
                                <span class="enterprise-avatar">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </span>
                            </div>
                            <div class="d-block text-start user-meta-block">
                                <div class="user-display-name">{{ Auth::user()->name }}</div>
                                <div class="user-display-role">{{ Auth::user()->roles->first()->name ?? 'Administrator' }}</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon dropdown-chevron-icon d-inline-block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6" /></svg>
                        </a>
                        
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 enterprise-user-dropdown">
                            <div class="dropdown-header px-3 py-2.5 rounded-top" style="background-color: var(--saham-surface-subtle); border-bottom: 1px solid var(--saham-border-blue);">
                                <div class="fw-bold text-dark fs-4">{{ Auth::user()->name }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;">{{ Auth::user()->email }}</div>
                                <span class="badge bg-blue-lt fw-bold mt-1.5">{{ Auth::user()->roles->first()->name ?? 'Administrator' }}</span>
                            </div>
                            <div class="dropdown-divider my-1"></div>
                            <a href="{{ route('profile') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2">
                                <span class="dropdown-item-icon bg-primary-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark">Profil Saya</div>
                                    <div class="small text-muted">Informasi akun & keamanan</div>
                                </div>
                            </a>
                            @if(Auth::user()->can('manage-settings') || Auth::user()->can('view-settings'))
                                <a href="{{ route('setting') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2">
                                    <span class="dropdown-item-icon bg-blue-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">Pengaturan Sistem</div>
                                        <div class="small text-muted">Kredensial API & preferensi</div>
                                    </div>
                                </a>
                            @endif
                            <a href="{{ route('logout') }}" class="dropdown-item d-flex align-items-center gap-2.5 py-2 text-danger">
                                <span class="dropdown-item-icon bg-danger-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                                </span>
                                <div>
                                    <div class="fw-semibold text-danger">Keluar (Logout)</div>
                                    <div class="small text-muted">Akhiri sesi akun Anda</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>