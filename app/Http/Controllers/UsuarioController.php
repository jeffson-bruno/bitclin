<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct()
    {
        // Se você já usa Spatie, prefira hasRole('admin') para consistência
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('admin') && auth()->user()->role !== 'admin') {
                abort(403, 'Acesso não autorizado.');
            }
            return $next($request);
        });
    }

    /**
     * Lista de usuários.
     */
    public function index()
    {
        $usuarios = User::with(['roles', 'especialidade'])
            ->get()
            ->map(function ($user) {
                // Monta string de registro profissional (ex.: CRM-PI 12345), se houver
                $registro = null;
                if ($user->registro_tipo && $user->registro_numero) {
                    $uf = $user->registro_uf ? '-' . strtoupper($user->registro_uf) : '';
                    $registro = $user->registro_tipo . $uf . ' ' . $user->registro_numero;
                }

                return [
                    'id'                   => $user->id,
                    'name'                 => $user->name,
                    'usuario'              => $user->usuario,
                    'role'                 => $user->getRoleNames()->first() ?? $user->role, // prioridade para Spatie
                    'especialidade'        => $user->especialidade->nome ?? null,
                    'registro_profissional'=> $registro,
                    'created_at'           => $user->created_at,
                ];
            });

        $especialidades = Especialidade::select('id', 'nome')->orderBy('nome')->get();

        return inertia('Usuarios/Index', [
            'usuarios'       => $usuarios,
            'especialidades' => $especialidades,
        ]);
    }

    /**
     * Cria novo usuário.
     */
    public function store(Request $request)
    {
        $rules = [
            'name'     => ['required','string','max:255'],
            'usuario'  => ['required','string','alpha_dash','min:3','max:50','unique:users,usuario'],
            'password' => ['required','string','confirmed','min:6','max:6'], // exatamente 6
            'role'     => ['required', Rule::in(['admin','receptionist','doctor'])],

            'especialidade_id' => ['nullable','exists:especialidades,id'],
            'registro_tipo'    => ['nullable','string','max:10'],
            'registro_numero'  => ['nullable','string','max:30'],
            'registro_uf'      => ['nullable','string','size:2','regex:/^[A-Za-z]{2}$/'],
        ];

        // Se for médico, os campos de conselho são obrigatórios
        if ($request->input('role') === 'doctor') {
            $rules['especialidade_id'] = ['required','exists:especialidades,id'];
            $rules['registro_tipo']    = ['required','string','max:10'];
            $rules['registro_numero']  = ['required','string','max:30'];
            $rules['registro_uf']      = ['required','string','size:2','regex:/^[A-Za-z]{2}$/'];
        }

        $data = $request->validate($rules);

        // Normaliza UF
        if (!empty($data['registro_uf'])) {
            $data['registro_uf'] = strtoupper($data['registro_uf']);
        }

        // Se não for médico, zera campos
        if ($data['role'] !== 'doctor') {
            $data['especialidade_id'] = null;
            $data['registro_tipo'] = null;
            $data['registro_numero'] = null;
            $data['registro_uf'] = null;
        }

        $role = $data['role'];           // mantém coluna role e papel do Spatie em sincronia
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $user->syncRoles([$role]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Atualiza usuário.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name'     => ['required','string','max:255'],
            'usuario'  => ['required','string','alpha_dash','min:3','max:50', Rule::unique('users','usuario')->ignore($user->id)],
            'role'     => ['required', Rule::in(['admin','receptionist','doctor'])],
            'password' => ['nullable','string','confirmed','min:6','max:6'],

            'especialidade_id' => ['nullable','exists:especialidades,id'],
            'registro_tipo'    => ['nullable','string','max:10'],
            'registro_numero'  => ['nullable','string','max:30'],
            'registro_uf'      => ['nullable','string','size:2','regex:/^[A-Za-z]{2}$/'],
        ];

        if ($request->input('role') === 'doctor') {
            $rules['especialidade_id'] = ['required','exists:especialidades,id'];
            $rules['registro_tipo']    = ['required','string','max:10'];
            $rules['registro_numero']  = ['required','string','max:30'];
            $rules['registro_uf']      = ['required','string','size:2','regex:/^[A-Za-z]{2}$/'];
        }

        $data = $request->validate($rules);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if (!empty($data['registro_uf'])) {
            $data['registro_uf'] = strtoupper($data['registro_uf']);
        }

        if ($data['role'] !== 'doctor') {
            $data['especialidade_id'] = null;
            $data['registro_tipo'] = null;
            $data['registro_numero'] = null;
            $data['registro_uf'] = null;
        }

        $user->update($data);

        // Spatie roles em sincronia com a coluna role
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Remove usuário.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    // create(), show(), edit() — se não usa views separadas, pode manter vazios
    public function create() {}
    public function show(string $id) {}
    public function edit(string $id) {}
}
