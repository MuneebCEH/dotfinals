<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        $users = User::query()
            // ✅ Apply search when q is present (trim to avoid spaces-only)
            ->when(trim((string) $request->q) !== '', function ($q) use ($request) {
                $term = '%' . trim($request->q) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('role', 'like', $term); // adjust if you store roles differently
                });
            })
            ->orderBy('name')
            // ✅ Keep query string during pagination so AJAX pagination stays filtered
            ->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }


    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        if (Auth::id() === $user->id) {
            abort(403, 'Cannot modify your own account');
        }

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            abort(403, 'Cannot delete your own account');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
