<!-- Native Android Bottom Navigation Dock (Visible on Mobile only) -->
<nav class="android-bottom-nav d-md-none" aria-label="Navigasi Aplikasi">
    <div class="android-bottom-nav-inner">
        <!-- 1. Dashboard / Portofolio (Berdasarkan Permission 'view-dashboard') -->
        @can('view-dashboard')
            <a href="{{ route('dashboard') }}" class="android-nav-tab {{ Route::is('dashboard') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>
                </div>
                <span class="android-tab-label">{{ !auth()->user()->can('view-shareholders') ? 'Portofolio' : 'Dashboard' }}</span>
            </a>
        @endcan

        <!-- 2. Direktori Portofolio Investor (Berdasarkan Permission 'view-shareholder-directory') -->
        @can('view-shareholder-directory')
            <a href="{{ route('investor-directory.index') }}" class="android-nav-tab {{ Route::is('investor-directory*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                </div>
                <span class="android-tab-label">Eksplor</span>
            </a>
        @endcan

        <!-- 3. Manajemen Investor (Berdasarkan Permission 'view-shareholders') -->
        @can('view-shareholders')
            <a href="{{ route('shareholders.index') }}" class="android-nav-tab {{ Route::is('shareholders*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </div>
                <span class="android-tab-label">Investor</span>
            </a>
        @endcan

        <!-- 4. Laporan Keuntungan Saham (Berdasarkan Permission 'view-investment-reports') -->
        @can('view-investment-reports')
            <a href="{{ route('investment-reports.index') }}" class="android-nav-tab {{ Route::is('investment-reports*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                </div>
                <span class="android-tab-label">Laporan</span>
            </a>
        @endcan

        <!-- 3. Master Data (Berdasarkan Permission 'lihat role' & 'lihat pengguna') -->
        @if(auth()->user()->can('lihat role') || auth()->user()->can('lihat pengguna'))
            <a href="{{ route('user.index') }}" class="android-nav-tab {{ request()->is('role*') || request()->is('users*') || request()->is('user*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M4 9h16" /><path d="M4 15h16" /><path d="M10 4v16" /></svg>
                </div>
                <span class="android-tab-label">Master Data</span>
            </a>
        @endif

        <!-- 4. Pengaturan (Berdasarkan Permission 'view-settings' / 'manage-settings') -->
        @if(auth()->user()->can('view-settings') || auth()->user()->can('manage-settings'))
            <a href="{{ route('setting') }}" class="android-nav-tab {{ Route::is('setting*') ? 'active' : '' }}">
                <div class="android-tab-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                </div>
                <span class="android-tab-label">Pengaturan</span>
            </a>
        @endif

        <!-- 5. Profil (Menu Default untuk Semua Akses Level) -->
        <a href="{{ route('profile') }}" class="android-nav-tab {{ Route::is('profile*') ? 'active' : '' }}">
            <div class="android-tab-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
            </div>
            <span class="android-tab-label">Profil</span>
        </a>
    </div>
</nav>
