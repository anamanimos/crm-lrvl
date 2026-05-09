<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('id', 'asc')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.form');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('roles.form', compact('role'));
    }

    public function permissions($id)
    {
        $role = Role::findOrFail($id);
        $permissions_grouped = Permission::orderBy('module', 'asc')->get()->groupBy('module');
        $role_permission_ids = $role->permissions()->pluck('permissions.id')->toArray();

        return view('roles.permissions', compact('role', 'permissions_grouped', 'role_permission_ids'));
    }

    public function save_permissions(Request $request)
    {
        $role = Role::findOrFail($request->role_id);
        $role->permissions()->sync($request->permissions ?: []);

        return response()->json(['success' => true, 'message' => 'Permissions berhasil disimpan']);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'Role sistem tidak dapat dihapus']);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Role masih digunakan oleh user']);
        }

        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role berhasil dihapus']);
    }

    public function toggle_status($id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'Role sistem tidak dapat dinonaktifkan']);
        }

        $role->is_active = !$role->is_active;
        $role->save();

        return response()->json(['success' => true]);
    }
}
