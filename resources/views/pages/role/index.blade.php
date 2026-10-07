@extends('layouts.app')

@section('pretitle', 'MASTER DATA')
@section('title', 'Level Akses')
@section('subtitle', 'Kelola tingkatan level akses dan konfigurasi hak akses fitur pengguna sistem secara dinamis.')

@section('actions')
    @can('tambah level akses')
        <a href="javascript:void(0)" id="addBtn" data-bs-toggle="modal" data-bs-target="#modal-simple" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            + Tambah Level
        </a>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm border bg-white">
    <div class="table-responsive p-3">
        <table id="table-roles" class="table card-table table-vcenter table-hover w-100">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Nama Level</th>
                    <th>Jumlah Pengguna</th>
                    <th>Hak Akses</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modal')
<div class="modal modal-blur fade" id="modal-simple" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border">
            <div class="modal-header py-3 px-4 bg-white border-bottom">
                <h5 class="modal-title fw-bold text-dark">Tambah Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <input type="hidden" name="type" id="type">
                <input type="hidden" name="id" id="id">
                <div class="mb-3">
                    <label for="name" class="form-label required">Nama Level</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Staf Keuangan / Auditor" required>
                    <span class="invalid-feedback error_name"></span>
                </div>
            </div>
            <div class="modal-footer py-3 px-4 bg-light-subtle border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="storeBtn" class="btn btn-primary px-4">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('js')
<script>
    const BASE = "{{ route('role.index') }}";

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    let table;
    $(document).ready(function() {
        table = $('#table-roles').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('role.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted fw-semibold' },
                { data: 'name_badge', name: 'name' },
                { data: 'users_count', name: 'users_count', orderable: false, searchable: false },
                { data: 'permissions_count', name: 'permissions_count', orderable: false, searchable: false },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });
    });

    $("#addBtn").click(function() {
        $(".modal-title").html("Tambah Level");
        $("#name").val("");
        $("#type").val("create");
        $("#id").val("");
        $("#name").removeClass('is-invalid');
        $(".error_name").html('');
    });

    $("#storeBtn").click(function() {
        let id = $("#id").val();
        let type = $("#type").val();
        let name = $("#name").val();

        let url;
        let method;

        if (type === 'create') {
            url = BASE + '/store';
            method = "POST";
        } else {
            url = BASE + `/${id}/update`;
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            method: method,
            data: {
                name: name
            },
        }).done(function(response) {
            if (response.errors) {
                $.each(response.errors, function(index, value) {
                    $("#name").addClass('is-invalid');
                    $(".error_" + index).html(value);

                    setTimeout(() => {
                        $("#name").removeClass('is-invalid');
                        $(".error_" + index).html('');
                    }, 3000);
                });
            } else if (response.status === 'error') {
                Toast.fire({
                    icon: 'error',
                    title: response.message
                });
            } else {
                $("#modal-simple").modal('hide');
                Toast.fire({
                    icon: response.status,
                    title: response.message
                });

                if (table) {
                    table.ajax.reload(null, false);
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    });

    function editModal(id) {
        let url = BASE + `/${id}/show`;
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            $(".modal-title").html("Edit Level");
            let data = response.data;
            $("#modal-simple").modal('show');

            $("#id").val(data.id);
            $("#name").val(data.name);
            $("#type").val("update");
            $("#name").removeClass('is-invalid');
            $(".error_name").html('');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function deleteType(id) {
        Swal.fire({
            title: "Konfirmasi Hapus",
            text: "Apakah Anda yakin ingin menghapus level ini?",
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
