<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Especialidade;


class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::with('roles')->get()->map(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'usuario'    => $user->usuario,
                'role'       => $user->getRoleNames()->first(), // Pega o primeiro papel
                'created_at' => $user->created_at,
            ];
        });

        $especialidades = Especialidade::select('id', 'nome')->get();

        return inertia('Usuarios/Index', [
            'usuarios' => $usuarios,
            'especialidades' => $especialidades,
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
            'password' => 'required|string|confirmed|min:6|max:6',
            'role'     => 'required|in:admin,receptionist,doctor',
            'especialidade_id' => 'nullable|exists:especialidades,id',
        ]);

        // Se for médico, garante que vai salvar a especialidade
        if ($validated['role'] === 'doctor') {
            $validated['especialidade_id'] = $request->especialidade_id;
        } else {
            $validated['especialidade_id'] = null;
        }

        // Remover o role antes de salvar no banco (caso a tabela users não tenha essa coluna)
        $role = $validated['role'];
        $validated['role'] = $role;


        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        // Atribuir corretamente o papel
        $user->syncRoles([$role]);

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
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'usuario' => 'required|string|unique:users,usuario,' . $user->id,
            'role' => 'required|in:admin,receptionist,doctor',
            'password' => 'nullable|string|confirmed|min:6|max:6',
        ]);

        $user->name = $request->name;
        $user->usuario = $request->usuario;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // Remove papéis anteriores e aplica o novo
        $user->syncRoles([$request->role]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
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

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Acesso não autorizado.');
            }
            return $next($request);
        });
    }

}
