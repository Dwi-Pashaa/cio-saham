@extends('layouts.app')

@section('pretitle', 'MASTER DATA')
@section('title', 'Data Pengguna (Users)')
@section('subtitle', 'Kelola akun pengelola, email akses, dan penugasan role level hak akses sistem.')

@section('actions')
    @can('tambah pengguna')
        <a href="{{ route('user.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            + Tambah Pengguna
        </a>
    @endcan
@endsection

@section('content')
@include('components.alert.success')

<div class="card shadow-sm border bg-white">
    <div class="table-responsive p-3">
        <table id="table-users" class="table card-table table-vcenter table-hover w-100">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Nama & Username</th>
                    <th>Email</th>
                    <th>Hak Akses / Role</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js')
<script>
    const BASE = "{{ route('user.index') }}";

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    let table;
    $(document).ready(function() {
        table = $('#table-users').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('user.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted fw-semibold' },
                { data: 'user_info', name: 'name' },
                { data: 'email_formatted', name: 'email' },
                { data: 'role_badge', name: 'roles.name', orderable: false },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });
    });

    function deleteUsers(id, name) {
        Swal.fire({
            title: "Konfirmasi Hapus",
            text: "Apakah Anda yakin ingin menghapus user " + (name || "") + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#1e40af",
            cancelButtonColor: "#ef4444",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE + '/' + id + '/destroy',
                    method: "DELETE",
                    dataType: "json",
                    success: function(response) {
                        Toast.fire({
                            icon: response.status,
                            title: response.message
                        });

                        if (table) {
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(err) {
                        Toast.fire({
                            icon: "error",
                            title: "Gagal menghapus data dari server."
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
