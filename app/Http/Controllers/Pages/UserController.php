<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('id', 'DESC');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('user_info', function ($item) {
                    $initials = strtoupper(substr($item->name, 0, 2));
                    $name = e($item->name);
                    $username = e($item->username);

                    return '<div class="d-flex align-items-center">
                                <div class="avatar avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold me-2.5 shadow-xs" style="width: 34px; height: 34px; font-size: 0.75rem;">
                                    ' . $initials . '
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">' . $name . '</div>
                                    <div class="small text-muted font-monospace">@<span>' . $username . '</span></div>
                                </div>
                            </div>';
                })
                ->addColumn('email_formatted', function ($item) {
                    return '<span class="text-secondary">' . e($item->email) . '</span>';
                })
                ->addColumn('role_badge', function ($item) {
                    $roleName = optional($item->roles->first())->name ?? 'User';
                    return '<span class="badge badge-soft-primary px-2.5 py-1 fw-bold">' . e($roleName) . '</span>';
                })
                ->addColumn('created_at_formatted', function ($item) {
                    return '<span class="text-muted small">' . \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') . ' WIB</span>';
                })
                ->addColumn('action', function ($item) {
                    $currentUser = auth()->user();
                    $editUrl = route('user.edit', ['id' => $item->id]);

                    $btnEdit = '';
                    if ($currentUser && $currentUser->can('ubah pengguna')) {
                        $btnEdit = '<a href="' . $editUrl . '" class="btn-action btn-action-warning" title="Edit Pengguna">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </a>';
                    }

                    $btnDelete = '';
                    if ($currentUser && $currentUser->can('hapus pengguna')) {
                        $btnDelete = '<button type="button" onclick="return deleteUsers(\'' . $item->id . '\', \'' . e($item->name) . '\')" class="btn-action btn-action-danger" title="Hapus Pengguna">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>';
                    }

                    return '<div class="action-btn-group d-flex justify-content-center align-items-center gap-1">' . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['user_info', 'email_formatted', 'role_badge', 'created_at_formatted', 'action'])
                ->make(true);
        }

        return view("pages.user.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = Role::orderBy('name', 'asc')->get();
        return view("pages.user.create", compact("role"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "username" => "required|unique:users,username",
            "name" => "required|string",
            "email" => "required|unique:users,email",
            "role" => "required|exists:roles,name",
            "password" => "required|string|min:8|confirmed",
            "password_confirmation" => "required|string"
        ]);

        $post = $request->except('password_confirmation', 'role');
        $post['password'] = Hash::make($request->password);

        $user = User::create($post);
        $user->assignRole($request->role);

        return redirect()->route('user.index')->with('success', 'Berhasil menambahkan user baru.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $role = Role::orderBy('name', 'asc')->get();
        return view("pages.user.edit", compact("user", "role"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            "username" => "required|unique:users,username," . $user->id,
            "name" => "required|string",
            "email" => "required|unique:users,email," . $user->id,
            "role" => "required|exists:roles,name",
            "password" => "nullable|string|min:8|confirmed",
        ]);
    
        $updateData = $request->except('password', 'password_confirmation', 'role');
    
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
    
        $user->update($updateData);
        $user->syncRoles([$request->role]);
    
        return redirect()->route('user.index')->with('success', 'Berhasil memperbarui user.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['code' => 400, 'status' => 'error', 'message' => 'Data Not Found.']);
        }

        $user->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }
}

