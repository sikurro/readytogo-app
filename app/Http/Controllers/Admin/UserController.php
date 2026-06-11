<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserExport;
use App\Exports\UserTemplateExport;
use App\Imports\UserImport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('nip', 'like', '%' . $search . '%')
                  ->orWhere('position', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $users = $query->latest()->paginate($perPage)->withQueryString();
        $roles = Role::all();
        $locations = User::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location');

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'roles' => $roles,
            'locations' => $locations,
            'filters' => $request->only(['search', 'role_id', 'location', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:users,nip',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'position' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status_fit' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'nip', 'email', 'role_id', 'position', 'location', 'status_fit']);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:users,nip,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'position' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status_fit' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean'
        ]);

        $data = $request->only(['name', 'nip', 'email', 'role_id', 'position', 'location', 'status_fit']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Detail user berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Password user berhasil direset.');
    }

    public function destroy(User $user)
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new UserTemplateExport, 'template_import_user.xlsx');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new UserExport($request->role_id, $request->location, $request->search),
            'data_petugas.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        Excel::import(new UserImport, $request->file('file'));

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diimport.');
    }
}
