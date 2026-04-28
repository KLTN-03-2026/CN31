<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhongBan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of system users with search and filter capabilities.
     * Accessible only by System Administrators.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = User::with('phongBan')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->paginate(15)->withQueryString(),
            'phongBans' => PhongBan::select('id', 'ten_phong_ban')->get(),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Store a newly created user in the system.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'vai_tro' => 'required|string',
            'phong_ban_id' => 'nullable|exists:phong_ban,id',
            'tong_ngay_phep' => 'required|numeric|min:0',
        ], [
            'email.unique' => 'Địa chỉ email này đã tồn tại trong hệ thống.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'vai_tro' => $validated['vai_tro'],
            'phong_ban_id' => $validated['phong_ban_id'],
            'tong_ngay_phep' => $validated['tong_ngay_phep'],
            'trang_thai' => true,
        ]);

        return back()->with('success', 'Đã thêm nhân viên mới thành công!');
    }

    /**
     * Update the specified user's information.
     * Prevents administrators from demoting or deactivating their own accounts.
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'vai_tro' => 'required|string',
            'phong_ban_id' => 'nullable|exists:phong_ban,id',
            'tong_ngay_phep' => 'required|numeric|min:0',
            'trang_thai' => 'required|boolean',
        ], [
            'email.unique' => 'Địa chỉ email này đã tồn tại.',
        ]);

        // Guard: Prevent self-demotion or self-deactivation
        if ($user->id === Auth::id()) {
            $validated['vai_tro'] = 'admin';
            $validated['trang_thai'] = true;
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return back()->with('success', 'Đã cập nhật thông tin nhân viên!');
    }

    /**
     * Remove the specified user from the system.
     */
    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Bạn không thể tự xóa chính mình!']);
        }

        try {
            $user->delete();

            return back()->with('success', 'Đã xóa tài khoản nhân viên khỏi hệ thống.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Không thể xóa do nhân viên đang có dữ liệu liên quan.']);
        }
    }
}
