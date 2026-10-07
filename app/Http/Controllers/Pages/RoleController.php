<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users')->with('permissions')->orderBy('id', 'DESC');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('name_badge', function ($item) {
                    $isSystem = strtolower($item->name) === 'admin';
                    $lockIcon = $isSystem ? ' <span title="Level Sistem (Terkunci)" class="text-warning"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg></span>' : '';
                    return '<span class="badge badge-soft-primary px-3 py-1 fw-bold fs-6">' . e($item->name) . '</span>' . $lockIcon;
                })
                ->addColumn('users_count', function ($item) {
                    return '<span class="badge bg-secondary-lt fw-bold font-monospace px-2.5 py-1">' . $item->users_count . ' Pengguna</span>';
                })
                ->addColumn('permissions_count', function ($item) {
                    $totalPermissions = Permission::count();
                    if (strtolower($item->name) === 'admin') {
                        return '<span class="badge bg-success-lt text-success fw-bold font-monospace px-2.5 py-1">Semua Izin (Full Akses)</span>';
                    }
                    return '<span class="badge bg-blue-lt fw-bold font-monospace px-2.5 py-1">' . $item->permissions->count() . ' dari ' . $totalPermissions . ' Izin</span>';
                })
                ->addColumn('created_at_formatted', function ($item) {
                    return '<span class="text-muted small">' . ($item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') . ' WIB' : '-') . '</span>';
                })
                ->addColumn('action', function ($item) {
                    $user = auth()->user();
                    $permissionUrl = route('role.permission', ['id' => $item->id]);
                    $isAdminRole = strtolower($item->name) === 'admin';

                    $btnPermission = '';
                    if ($user && $user->can('atur hak akses')) {
                        $btnPermission = '<a href="' . $permissionUrl . '" class="btn-action btn-action-primary" title="Atur Hak Akses / Permission">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-6a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /><path d="M20 21l2 -2l-2 -2" /><path d="M17 17l-2 2l2 2" /></svg>
                                        </a>';
                    }

                    $btnEdit = '';
                    if ($user && $user->can('ubah level akses') && !$isAdminRole) {
                        $btnEdit = '<a href="javascript:void(0)" onclick="return editModal(\'' . $item->id . '\')" class="btn-action btn-action-warning" title="Edit Level">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </a>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus level akses') && !$isAdminRole) {
                        $btnDelete = '<button type="button" onclick="return deleteType(\'' . $item->id . '\')" class="btn-action btn-action-danger" title="Hapus Level">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>';
                    }

                    return '<div class="action-btn-group d-flex justify-content-center align-items-center gap-1">' . $btnPermission . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['name_badge', 'users_count', 'permissions_count', 'created_at_formatted', 'action'])
                ->make(true);
        }

        return view("pages.role.index");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "name" => "required|string|max:255|unique:roles,name",
        ], [
            'name.required' => 'Nama level wajib diisi.',
            'name.unique' => 'Nama level sudah digunakan.',
        ]);

        if ($validation->fails()) {
            return response()->json(['code' => 400, 'errors' => $validation->errors()]);
        }

        Role::create([
            'name' => trim($request->name),
            'guard_name' => 'web',
        ]);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil membuat level baru.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $roles = Role::find($id);

        if (!$roles) {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $roles]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $roles = Role::find($id);

        if (!$roles) {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        if (strtolower($roles->name) === 'admin') {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Level Admin adalah level sistem dan tidak dapat diubah namanya.']);
        }

        $validation = Validator::make($request->all(), [
            "name" => "required|string|max:255|unique:roles,name," . $roles->id,
        ], [
            'name.required' => 'Nama level wajib diisi.',
            'name.unique' => 'Nama level sudah digunakan.',
        ]);

        if ($validation->fails()) {
            return response()->json(['code' => 400, 'errors' => $validation->errors()]);
        }

        $roles->update(['name' => trim($request->name)]);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil memperbarui level.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $roles = Role::find($id);

        if (!$roles) {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }

        if (strtolower($roles->name) === 'admin') {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Level Admin adalah level sistem dan tidak dapat dihapus.']);
        }

        if ($roles->users()->count() > 0) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Level tidak dapat dihapus karena masih digunakan oleh ' . $roles->users()->count() . ' pengguna. Pindahkan pengguna ke level lain terlebih dahulu.'
            ]);
        }
        
        $roles->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus level.']);
    }

    public function permission(string $id) 
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('id')->get();    
        return view("pages.role.permission", compact("role", "permissions"));
    }

    public function savePermission(Request $request, string $id) 
    {
        $role = Role::findOrFail($id);

        if (strtolower($role->name) === 'admin') {
            return back()->with('error', 'Hak akses level Admin selalu mencakup seluruh izin sistem dan tidak dapat dikurangi.');
        }

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Berhasil menyimpan hak akses untuk level ' . $role->name);     
    }
}

