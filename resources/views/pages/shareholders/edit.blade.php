@extends('layouts.app')

@section('pretitle', 'MANAJEMEN EKUITAS')
@section('title', 'Edit Pemilik Saham: ' . $shareholder->name)
@section('subtitle', 'Perbarui data identitas dan status pemilik saham.')

@section('actions')
    <a href="{{ route('shareholders.show', $shareholder->id) }}" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali ke Detail
    </a>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <h4 class="alert-title fw-bold">Terdapat kesalahan input:</h4>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border shadow-sm bg-white">
            <div class="card-header border-bottom py-3 px-4 bg-white">
                <h3 class="card-title fw-bold mb-0 text-dark">Form Edit Data Pemilik Saham</h3>
            </div>
            <form action="{{ route('shareholders.update', $shareholder->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label required">Nama Lengkap Pemilik Saham</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $shareholder->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nomor Identitas (NIK / KTP)</label>
                        <input type="text" name="id_card_number" class="form-control font-monospace" value="{{ old('id_card_number', $shareholder->id_card_number) }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $shareholder->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $shareholder->phone) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $shareholder->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $shareholder->notes) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $shareholder->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $shareholder->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="card-footer bg-light-subtle d-flex justify-content-between align-items-center py-3 px-4 border-top">
                    <a href="{{ route('shareholders.show', $shareholder->id) }}" class="btn btn-outline-secondary px-3">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
