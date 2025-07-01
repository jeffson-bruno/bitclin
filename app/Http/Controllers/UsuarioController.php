<?php

namespace App\Http\Controllers;
use App\Models\User;


use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::select('id', 'name', 'usuario', 'role', 'created_at')->get();

        return inertia('Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'usuario'  => 'required|string|unique:users,usuario',
            'password' => 'required|string|confirmed|min:6',
            'role'     => 'required|in:admin,receptionist,doctor',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        // email fica nulo
        User::create($validated);

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'usuario' => 'required|string|unique:users,usuario,' . $id,
            'role'    => 'required|in:admin,receptionist,doctor',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        User::findOrFail($id)->update($validated);

        return redirect()->route('usuarios.index')
                        ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso.');
    }
}
