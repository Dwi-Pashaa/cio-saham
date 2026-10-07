<header class="navbar-expand-md enterprise-navbar-wrapper d-none d-md-block">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar enterprise-subnav">
            <div class="container-xl">
                <div class="row flex-fill align-items-center">
                    <div class="col">
                        <ul class="navbar-nav enterprise-nav-list">
                            <!-- 1. Dashboard -->
                            @can('lihat dashboard')
                                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('dashboard') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            {{ !auth()->user()->can('lihat dashboard perusahaan') ? 'Portofolio Saya' : 'Dashboard' }}
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 2. Master Data (Dropdown: Pengguna & Level Akses) -->
                            @canany(['lihat pengguna', 'lihat level akses'])
                                <li class="nav-item dropdown {{ Route::is('role*') || Route::is('user*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M4 9h16" /><path d="M4 15h16" /><path d="M10 4v16" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Master Data
                                        </span>
                                    </a>
                                    <div class="dropdown-menu shadow-lg border-0 py-2.5 enterprise-dropdown-menu">
                                        @can('lihat pengguna')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('user*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                                                <span class="dropdown-icon-box bg-azure-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-azure"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Pengguna</div>
                                                    <div class="small text-muted mt-0.5">Akun administrator & staf</div>
                                                </div>
                                            </a>
                                        @endcan

                                        @can('lihat level akses')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('role*') ? 'active' : '' }}" href="{{ route('role.index') }}">
                                                <span class="dropdown-icon-box bg-blue-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Level Akses</div>
                                                    <div class="small text-muted mt-0.5">Hak akses & perizinan level dinamis</div>
                                                </div>
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            <!-- 3. Manajemen Investor (Dropdown: Data Investor, Portofolio Investor, Laporan Imbal Hasil) -->
                            @canany(['lihat investor', 'lihat portofolio investor', 'lihat laporan imbal hasil'])
                                <li class="nav-item dropdown {{ Route::is('shareholders*') || Route::is('investor-directory*') || Route::is('investment-reports*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link dropdown-toggle" href="#navbar-investor" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Manajemen Investor
                                        </span>
                                    </a>
                                    <div class="dropdown-menu shadow-lg border-0 py-2.5 enterprise-dropdown-menu">
                                        @can('lihat investor')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('shareholders*') ? 'active' : '' }}" href="{{ route('shareholders.index') }}">
                                                <span class="dropdown-icon-box bg-purple-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-purple"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Data Investor</div>
                                                    <div class="small text-muted mt-0.5">Kelola data pemegang saham & portofolio</div>
                                                </div>
                                            </a>
                                        @endcan

                                        @can('lihat portofolio investor')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('investor-directory*') ? 'active' : '' }}" href="{{ route('investor-directory.index') }}">
                                                <span class="dropdown-icon-box bg-blue-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Portofolio Investor</div>
                                                    <div class="small text-muted mt-0.5">Direktori portofolio seluruh pemegang saham</div>
                                                </div>
                                            </a>
                                        @endcan

                                        @can('lihat laporan imbal hasil')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('investment-reports*') ? 'active' : '' }}" href="{{ route('investment-reports.index') }}">
                                                <span class="dropdown-icon-box bg-green-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Laporan Imbal Hasil</div>
                                                    <div class="small text-muted mt-0.5">Rekapitulasi dividen & pembagian keuntungan</div>
                                                </div>
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            <!-- 4. Transaksi Kas (Dropdown: Pemasukan & Pengeluaran) -->
                            @canany(['lihat pemasukan', 'lihat pengeluaran'])
                                <li class="nav-item dropdown {{ Route::is('cash-incomes*') || Route::is('cash-outcomes*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link dropdown-toggle" href="#navbar-kas" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                        <span class="nav-link-icon d-inline-flex align-items-center text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Transaksi Kas
                                        </span>
                                    </a>
                                    <div class="dropdown-menu shadow-lg border-0 py-2.5 enterprise-dropdown-menu">
                                        @can('lihat pemasukan')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('cash-incomes*') ? 'active' : '' }}" href="{{ route('cash-incomes.index') }}">
                                                <span class="dropdown-icon-box bg-green-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Pemasukan</div>
                                                    <div class="small text-muted mt-0.5">Catatan saldo masuk PT CIO NETWORK</div>
                                                </div>
                                            </a>
                                        @endcan

                                        @can('lihat pengeluaran')
                                            <a class="dropdown-item d-flex align-items-center gap-3 py-2.5 px-3 {{ Route::is('cash-outcomes*') ? 'active' : '' }}" href="{{ route('cash-outcomes.index') }}">
                                                <span class="dropdown-icon-box bg-red-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-danger"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /><path d="M15 15l3 3l3 -3" /></svg>
                                                </span>
                                                <div class="dropdown-text-wrap">
                                                    <div class="fw-semibold text-dark fs-4">Pengeluaran</div>
                                                    <div class="small text-muted mt-0.5">Catatan saldo keluar & belanja aset</div>
                                                </div>
                                            </a>
                                         @endcan
                                     </div>
                                 </li>
                             @endcanany

                            <!-- 5. Inventaris Aset -->
                            @can('lihat aset')
                                <li class="nav-item {{ Route::is('assets*') ? 'active' : '' }}">
                                    <a class="nav-link enterprise-nav-link" href="{{ route('assets.index') }}">
                                        <span class="nav-link-icon d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                                        </span>
                                        <span class="nav-link-title">
                                            Aset Perusahaan
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            <!-- 6. Pengaturan Sistem -->
                            @can('lihat pengaturan')
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
                            @endcan
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>