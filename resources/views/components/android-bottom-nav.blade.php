<!-- Native Android Bottom Navigation Dock (Visible on Mobile only) -->
<nav class="android-bottom-nav d-md-none" aria-label="Navigasi Aplikasi">
    <div class="android-bottom-nav-inner">
        <!-- 1. Dashboard / Portofolio -->
        @can('lihat dashboard')
            <a href="{{ route('dashboard') }}" class="android-nav-tab {{ Route::is('dashboard') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>
                </div>
                <span class="android-tab-label">{{ !auth()->user()->can('lihat dashboard perusahaan') ? 'Portofolio' : 'Dashboard' }}</span>
            </a>
        @endcan

        <!-- 2. Pemasukan (Saldo Masuk) -->
        @can('lihat pemasukan')
            <a href="{{ route('cash-incomes.index') }}" class="android-nav-tab {{ Route::is('cash-incomes*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </div>
                <span class="android-tab-label text-success">Masuk</span>
            </a>
        @endcan

        <!-- 3. Pengeluaran (Saldo Keluar) -->
        @can('lihat pengeluaran')
            <a href="{{ route('cash-outcomes.index') }}" class="android-nav-tab {{ Route::is('cash-outcomes*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /><path d="M15 15l3 3l3 -3" /></svg>
                </div>
                <span class="android-tab-label text-danger">Keluar</span>
            </a>
        @endcan

        <!-- 4. Manajemen Investor -->
        @can('lihat investor')
            <a href="{{ route('shareholders.index') }}" class="android-nav-tab {{ Route::is('shareholders*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </div>
                <span class="android-tab-label">Investor</span>
            </a>
        @endcan
    </div>
</nav>
