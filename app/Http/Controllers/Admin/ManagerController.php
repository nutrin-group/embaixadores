<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::where('type', User::TYPE_MANAGER)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Managers/Index', [
            'managers' => $managers
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Managers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => User::TYPE_MANAGER,
            'status' => 'active',
            'manager_referral' => null
        ]);

        return redirect()->route('admin.managers.index')
            ->with('success', 'Manager criado com sucesso!');
    }

    public function edit(User $manager)
    {
        return Inertia::render('Admin/Managers/Edit', [
            'manager' => $manager
        ]);
    }

    public function update(Request $request, User $manager)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $manager->id,
        ]);

        $manager->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()]
            ]);

            $manager->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()->route('admin.managers.index')
            ->with('success', 'Manager atualizado com sucesso!');
    }

    public function destroy(User $manager)
    {
        $manager->delete();

        return redirect()->route('admin.managers.index')
            ->with('success', 'Manager removido com sucesso!');
    }
}
