@extends('layouts.app')

@section('pretitle', 'PENGATURAN OTORISASI')
@section('title', 'Hak Akses Level: ' . $role->name)
@section('subtitle', 'Konfigurasi izin fitur dan batasan menu sistem untuk level pengguna ini.')

@php
    $isAdminRole = strtolower($role->name) === 'admin';
@endphp

@section('actions')
    @if(!$isAdminRole)
        <button type="button" id="toggleAllBtn" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11l3 3l8 -8" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>
            <span id="toggleAllText">Pilih Semua Akses</span>
        </button>
    @endif
    <a href="{{ route('role.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-none">
        &larr; Kembali ke Daftar
    </a>
@endsection

@section('content') 
@include('components.alert.success')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-danger flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" /></svg>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($isAdminRole)
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background-color: #eff6ff; border-left: 4px solid #3b82f6 !important;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
        <div>
            <div class="fw-bold text-dark fs-4">Level Sistem (Super-Admin)</div>
            <div class="small text-muted mt-0.5">Level <strong>Admin</strong> selalu memiliki semua hak akses secara otomatis (termasuk izin baru di masa depan). Pengaturan hak akses untuk level Admin bersifat permanen dan tidak dapat dikurangi.</div>
        </div>
    </div>
@endif

@php
    $groupIcons = [
        'Dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>',
        'Pengguna' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-azure"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>',
        'Level Akses' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>',
        'Data Investor' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-purple"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
        'Kepemilikan Saham' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-yellow"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>',
        'Laporan Imbal Hasil' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>',
        'Portofolio Investor' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-blue"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>',
        'Pemasukan' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>',
        'Pengeluaran' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-danger"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /><path d="M15 15l3 3l3 -3" /></svg>',
        'Aset' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-azure"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>',
        'Pengaturan' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>',
    ];

    $groupedPermissions = $permissions->groupBy(function ($item) {
        $name = $item->name;
        if ($name === 'atur hak akses' || str_contains($name, 'level akses')) {
            return 'Level Akses';
        }
        if (str_contains($name, 'dashboard')) {
            return 'Dashboard';
        }
        if (str_contains($name, 'pengguna')) {
            return 'Pengguna';
        }
        if (str_contains($name, 'portofolio investor')) {
            return 'Portofolio Investor';
        }
        if (str_contains($name, 'laporan imbal hasil')) {
            return 'Laporan Imbal Hasil';
        }
        if (str_contains($name, 'kepemilikan saham')) {
            return 'Kepemilikan Saham';
        }
        if (str_contains($name, 'investor')) {
            return 'Data Investor';
        }
        if (str_contains($name, 'pemasukan')) {
            return 'Pemasukan';
        }
        if (str_contains($name, 'pengeluaran')) {
            return 'Pengeluaran';
        }
        if (str_contains($name, 'aset')) {
            return 'Aset';
        }
        if (str_contains($name, 'pengaturan')) {
            return 'Pengaturan';
        }
        $parts = explode(' ', $name, 2);
        return ucwords($parts[1] ?? $parts[0]);
    });

    function getPermissionDescription($name) {
        if ($name === 'lihat dashboard') return 'Membuka halaman Dashboard / Portofolio Utama';
        if ($name === 'lihat dashboard perusahaan') return 'Melihat ringkasan ekuitas & finansial seluruh perusahaan';
        if ($name === 'lihat pengguna') return 'Melihat daftar akun dan profil pengguna';
        if ($name === 'tambah pengguna') return 'Membuat akun pengguna baru dan memilih levelnya';
        if ($name === 'ubah pengguna') return 'Mengedit data akun pengguna dan mengganti levelnya';
        if ($name === 'hapus pengguna') return 'Menghapus akun pengguna dari sistem';
        if ($name === 'lihat level akses') return 'Membuka sub-menu Level Akses';
        if ($name === 'tambah level akses') return 'Membuat level akses baru';
        if ($name === 'ubah level akses') return 'Mengganti nama level akses';
        if ($name === 'atur hak akses') return 'Mencentang dan mengatur izin pada sebuah level';
        if ($name === 'hapus level akses') return 'Menghapus level akses';
        if ($name === 'lihat investor') return 'Melihat daftar dan detail data pemilik saham';
        if ($name === 'tambah investor') return 'Mendaftarkan data pemilik saham baru';
        if ($name === 'ubah investor') return 'Mengedit profil pemilik saham';
        if ($name === 'hapus investor') return 'Menghapus data pemilik saham beserta portofolionya';
        if ($name === 'lihat kepemilikan saham') return 'Melihat data instrumen kepemilikan saham';
        if ($name === 'tambah kepemilikan saham') return 'Menambah lembar/alokasi saham ke investor';
        if ($name === 'ubah kepemilikan saham') return 'Mengubah data nominal atau lembar saham';
        if ($name === 'hapus kepemilikan saham') return 'Menghapus data kepemilikan saham';
        if ($name === 'lihat laporan imbal hasil') return 'Melihat laporan pembagian laba / dividen tahunan';
        if ($name === 'tambah laporan imbal hasil') return 'Membuat laporan keuntungan saham baru';
        if ($name === 'ubah laporan imbal hasil') return 'Mengedit catatan laporan keuntungan saham';
        if ($name === 'hapus laporan imbal hasil') return 'Menghapus catatan laporan keuntungan saham';
        if ($name === 'lihat portofolio investor') return 'Membuka direktori portofolio seluruh pemegang saham';
        if ($name === 'lihat pemasukan') return 'Melihat daftar dan detail catatan saldo masuk';
        if ($name === 'tambah pemasukan') return 'Mencatat transaksi uang masuk baru';
        if ($name === 'ubah pemasukan') return 'Mengedit catatan transaksi uang masuk';
        if ($name === 'hapus pemasukan') return 'Menghapus catatan transaksi uang masuk';
        if ($name === 'lihat pengeluaran') return 'Melihat daftar dan detail catatan saldo keluar';
        if ($name === 'tambah pengeluaran') return 'Mencatat transaksi uang keluar baru';
        if ($name === 'ubah pengeluaran') return 'Mengedit catatan transaksi uang keluar';
        if ($name === 'hapus pengeluaran') return 'Menghapus catatan transaksi uang keluar';
        if ($name === 'lihat aset') return 'Membuka halaman inventaris dan rincian data aset';
        if ($name === 'tambah aset') return 'Mencatat data aset atau perangkat baru';
        if ($name === 'ubah aset') return 'Mengedit data nama, tipe, harga, SN, atau MAC address';
        if ($name === 'hapus aset') return 'Menghapus data aset dari sistem';
        if ($name === 'atur pemilik aset') return 'Mengatur kepemilikan aset (Milik PT vs Pemegang Saham)';
        if ($name === 'lihat pengaturan') return 'Membuka halaman pengaturan sistem';
        if ($name === 'ubah pengaturan') return 'Menyimpan perubahan konfigurasi pengaturan sistem';

        $parts = explode(' ', $name, 2);
        $act = $parts[0];
        $obj = $parts[1] ?? '';
        if ($act === 'lihat') return 'Melihat daftar & detail ' . $obj;
        if ($act === 'tambah') return 'Menambah ' . $obj . ' baru';
        if ($act === 'ubah') return 'Mengubah data ' . $obj;
        if ($act === 'hapus') return 'Menghapus data ' . $obj;
        if ($act === 'atur') return 'Mengatur ' . $obj;
        return 'Akses fitur ' . $name;
    }
@endphp

<div class="card shadow-sm border bg-white">
    <form action="{{ route('role.savePermission', ['id' => $role->id]) }}" method="POST">
        @csrf
        @method("PUT")
        
        <div class="card-body p-4 bg-light-subtle">
            <div class="row g-4">
                @foreach ($groupedPermissions as $groupName => $items)
                    <div class="col-lg-6 col-12">
                        <div class="card shadow-none border bg-white h-100" style="border-radius: 10px;">
                            <!-- Group Header -->
                            <div class="card-header py-2.5 px-3 bg-white d-flex justify-content-between align-items-center border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    {!! $groupIcons[$groupName] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /></svg>' !!}
                                    <span class="fw-bold text-dark fs-4">{{ $groupName }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-muted small fw-normal">
                                        {{ $items->count() }} Izin
                                    </span>
                                    @if(!$isAdminRole)
                                        <button type="button" class="btn btn-sm btn-ghost-primary px-2 py-0.5 btn-toggle-group" style="font-size: 0.75rem;" title="Pilih/Batal semua pada grup ini">
                                            Pilih Semua
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <!-- Group Permissions List -->
                            <div class="card-body p-3 bg-white">
                                <div class="row g-2">
                                    @foreach ($items as $item)
                                        @php
                                            $isChecked = $isAdminRole || $role->hasPermissionTo($item->name);
                                            $pName = strtolower($item->name);
                                            $parts = explode(' ', $pName, 2);
                                            $action = $parts[0];
                                            
                                            $pIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>';
                                            if ($action === 'tambah') {
                                                $pIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>';
                                            } elseif ($action === 'ubah') {
                                                $pIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>';
                                            } elseif ($action === 'hapus') {
                                                $pIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /></svg>';
                                            } elseif ($action === 'atur') {
                                                $pIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-6a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>';
                                            }

                                            $description = getPermissionDescription($item->name);
                                        @endphp
                                        <div class="col-12">
                                            <div class="permission-item p-2.5 border rounded-2 d-flex align-items-center justify-content-between {{ $isChecked ? 'bg-primary-subtle border-primary-subtle' : 'bg-white' }}" style="transition: all 0.15s ease-in-out;">
                                                <label class="form-check form-switch m-0 d-flex align-items-start justify-content-between w-100 cursor-pointer">
                                                    <div class="d-flex align-items-start gap-1 pe-2">
                                                        <span class="mt-0.5">{!! $pIcon !!}</span>
                                                        <div>
                                                            <div class="fw-bold text-dark small text-capitalize font-monospace" style="letter-spacing: 0.01em;">
                                                                {{ $item->name }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 0.74rem; line-height: 1.25;">
                                                                {{ $description }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input class="form-check-input permission-checkbox flex-shrink-0 mt-1" 
                                                           name="permissions[]" 
                                                           value="{{ $item->name }}" 
                                                           type="checkbox" 
                                                           {{ $isChecked ? 'checked' : '' }}
                                                           {{ $isAdminRole ? 'disabled' : '' }}>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 px-4 border-top">
            <a href="{{ route('role.index') }}" class="btn btn-outline-secondary px-3">
                Batal
            </a>
            @if(!$isAdminRole)
                <button type="submit" class="btn btn-primary px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                    Simpan Hak Akses
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Toggle item styling on check/uncheck
        $('.permission-checkbox').on('change', function() {
            var item = $(this).closest('.permission-item');
            if ($(this).is(':checked')) {
                item.addClass('bg-primary-subtle border-primary-subtle');
            } else {
                item.removeClass('bg-primary-subtle border-primary-subtle');
            }
        });

        // Toggle all per group
        $('.btn-toggle-group').on('click', function() {
            var card = $(this).closest('.card');
            var checkboxes = card.find('.permission-checkbox:not(:disabled)');
            var allChecked = checkboxes.length > 0 && checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked).trigger('change');
            $(this).text(allChecked ? 'Pilih Semua' : 'Batal Semua');
        });

        // Master toggle all permissions
        var allSelected = false;
        $('#toggleAllBtn').on('click', function() {
            allSelected = !allSelected;
            $('.permission-checkbox:not(:disabled)').prop('checked', allSelected).trigger('change');
            
            if (allSelected) {
                $('#toggleAllText').text('Batalkan Semua Akses');
                $('.btn-toggle-group').text('Batal Semua');
            } else {
                $('#toggleAllText').text('Pilih Semua Akses');
                $('.btn-toggle-group').text('Pilih Semua');
            }
        });
    });
</script>
@endpush