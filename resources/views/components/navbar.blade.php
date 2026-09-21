<header class="navbar-expand-md enterprise-navbar-wrapper d-none d-md-block">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar enterprise-subnav">
            <div class="container-xl">
                <div class="row flex-fill align-items-center">
                    <div class="col">
                        <ul class="navbar-nav enterprise-nav-list">
                            <!-- 1. Dashboard (Berdasarkan Permission 'view-dashboard') -->
                            @can('view-dashboard')
                                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('dashboard') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            {{ !auth()->user()->can('view-shareholders') ? 'Portofolio Saya' : 'Dashboard' }}
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 2. Direktori Portofolio Investor (Berdasarkan Permission 'view-shareholder-directory') -->
                            @can('view-shareholder-directory')
                                <li class="nav-item {{ Route::is('investor-directory*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('investor-directory.index') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Portofolio Investor
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 3. Master Data (Berdasarkan Permission 'lihat role' & 'lihat pengguna') -->
                            @if(auth()->user()->can('lihat role') || auth()->user()->can('lihat pengguna'))
                                <li class="nav-item dropdown {{ request()->is('role*') || request()->is('users*') || request()->is('user*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M4 9h16" /><path d="M4 15h16" /><path d="M10 4v16" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Master Data
                                        </span>
                                    </a>
                                    <div class="dropdown-menu shadow-lg border-0 py-2.5 enterprise-dropdown-menu">
                                        @can('lihat role')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('role*') ? 'active' : '' }}" href="{{ route('role.index') }}">
                                                <span class="dropdown-icon-box bg-blue-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Data Level & Role</div>
                                                    <div class="small text-muted mt-0.5">Hak akses & perizinan sistem</div>
                                                </div>
                                            </a>
                                        @endcan

                                        @can('lihat pengguna')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('user*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                                                <span class="dropdown-icon-box bg-azure-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-azure"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Data Pengguna (Users)</div>
                                                    <div class="small text-muted mt-0.5">Akun administrator & staf</div>
                                                </div>
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endif

                            <!-- 4. Manajemen Investor (Berdasarkan Permission 'view-shareholders') -->
                            @can('view-shareholders')
                                <li class="nav-item {{ Route::is('shareholders*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('shareholders.index') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Manajemen Investor
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 5. Laporan Keuntungan Saham (Berdasarkan Permission 'view-investment-reports') -->
                            @can('view-investment-reports')
                                <li class="nav-item {{ Route::is('investment-reports*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('investment-reports.index') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Laporan Imbal Hasil
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 4. Pengaturan Sistem (Berdasarkan Permission 'manage-settings' / 'view-settings') -->
                            @if(auth()->user()->can('manage-settings') || auth()->user()->can('view-settings'))
                                <li class="nav-item {{ Route::is('setting') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('setting') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Pengaturan
                                        </span>
                                    </a>
                                </li>
                            @endif

                            <!-- 5. Profil Saya (Menu Default untuk Semua Akses Level) -->
                            <li class="nav-item {{ Route::is('profile*') ? 'active' : '' }}">
                                <a class="nav-link enterprise-nav-link" href="{{ route('profile') }}">
                                    <span class="nav-link-icon d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                                    </span>
                                    <span class="nav-link-title">
                                        Profil Saya
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>