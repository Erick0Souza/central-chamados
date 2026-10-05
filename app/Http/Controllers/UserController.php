<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('users.index', ['users' => User::orderBy('name')->paginate(12)]);
    }

    public function store(Request $request)
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email', '')))]);
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()], 'role' => ['required', Rule::in(['cliente', 'tecnico', 'admin'])]]);
        $user = new User($data);
        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Usuário criado com sucesso.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Você não pode remover a própria conta.']);
        }

        $hasHistory = DB::table('tickets')->where('user_id', $user->id)->exists()
            || DB::table('comments')->where('user_id', $user->id)->exists()
            || DB::table('activities')->where('user_id', $user->id)->exists()
            || DB::table('attachments')->where('user_id', $user->id)->exists();

        if ($hasHistory) {
            return back()->withErrors(['user' => 'Este usuário possui histórico no sistema e não pode ser removido.']);
        }

        $user->delete();

        return back()->with('success', 'Usuário removido com sucesso.');
    }
}
