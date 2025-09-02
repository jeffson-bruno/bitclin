<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    // Centraliza os papéis válidos (PT-BR conforme seu seeder)
    private const ALLOWED_ROLES = ['admin','receptionist','doctor','enfermeiro','psicologo'];

    public function __construct()
    {
        // Restringe a Admin (Spatie ou coluna legacy)
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || (!auth()->user()->hasRole('admin') && auth()->user()->role !== 'admin')) {
                abort(403, 'Acesso não autorizado.');
            }
            return $next($request);
        });
    }

    /** Helpers **/
    private function needsCouncil(string $role): bool
    {
        return in_array($role, ['doctor','enfermeiro','psicologo'], true);
    }

    private function councilCodeFor(string $role): ?string
    {
        return match ($role) {
            'doctor'     => 'CRM',
            'enfermeiro' => 'COREN',
            'psicologo'  => 'CRP',
            default      => null,
        };
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

                // papel preferindo Spatie; fallback coluna legacy
                $roleKey = $user->getRoleNames()->first() ?? $user->role;

                return [
                    'id'                    => $user->id,
                    'name'                  => $user->name,
                    'usuario'               => $user->usuario,
                    'role'                  => $roleKey, // <- usa a variável
                    'especialidade'         => $user->especialidade->nome ?? null,
                    'especialidade_id'      => $user->especialidade_id,
                    'registro_tipo'         => $user->registro_tipo,
                    'registro_numero'       => $user->registro_numero,
                    'registro_uf'           => $user->registro_uf,
                    'registro_profissional' => $registro,
                    'created_at'            => $user->created_at,
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
        $request->merge([
            'usuario' => preg_replace('/\s+/u', ' ', trim((string) $request->input('usuario')))
        ]);

        $rules = [
            'name'     => ['required','string','max:255'],
            'usuario'  => [
                'required','string','min:3','max:50',
                'regex:/^[\pL\pN._-]+(?: [\pL\pN._-]+)*$/u',
                'unique:users,usuario',
            ],
            'password' => ['required','string','confirmed','min:6','max:6'], // exatamente 6
            'role'     => ['required', Rule::in(self::ALLOWED_ROLES)],

            'especialidade_id' => ['nullable','exists:especialidades,id'],
            'registro_tipo'    => ['nullable','string','max:10'],
            'registro_numero'  => ['nullable','string','max:30'],
            'registro_uf'      => ['nullable','string','size:2','regex:/^[A-Za-z]{2}$/'],
        ];

        $messages = [
            'usuario.regex'  => 'Use apenas letras, números, ponto (.), hífen (-), underline (_) e espaços simples (sem espaço no início/fim).',
            'usuario.unique' => 'Este Usuário já está em uso.',
        ];

        $role = (string) $request->input('role');

        // Regras condicionais
        if ($role === 'doctor') {
            $rules['especialidade_id'] = ['required','exists:especialidades,id'];
        }
        if ($this->needsCouncil($role)) {
            $rules['registro_numero'] = ['required','string','max:30'];
            $rules['registro_uf']     = ['required','string','size:2','regex:/^[A-Za-z]{2}$/'];
            // registro_tipo será normalizado abaixo (CRM/COREN/CRP) se não vier
        }

        $data = $request->validate($rules, $messages);

        // Normalizações
        if (!empty($data['registro_uf'])) {
            $data['registro_uf'] = strtoupper($data['registro_uf']);
        }

        if ($this->needsCouncil($role)) {
            $data['registro_tipo'] = strtoupper($data['registro_tipo'] ?? $this->councilCodeFor($role));
        } else {
            // Se não precisa de conselho, zera campos de conselho/especialidade
            $data['especialidade_id'] = null;
            $data['registro_tipo'] = null;
            $data['registro_numero'] = null;
            $data['registro_uf'] = null;
        }

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        // Mantém Spatie e a coluna "role" (se existir em $fillable) em sincronia
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

        $request->merge([
            'usuario' => preg_replace('/\s+/u', ' ', trim((string) $request->input('usuario')))
        ]);

        $rules = [
            'name'     => ['required','string','max:255'],
            'usuario'  => [
                'required','string','min:3','max:50',
                'regex:/^[\pL\pN._-]+(?: [\pL\pN._-]+)*$/u',
                Rule::unique('users','usuario')->ignore($user->id),
            ],
            'role'     => ['required', Rule::in(self::ALLOWED_ROLES)],
            'password' => ['nullable','string','confirmed','min:6','max:6'],

            'especialidade_id' => ['nullable','exists:especialidades,id'],
            'registro_tipo'    => ['nullable','string','max:10'],
            'registro_numero'  => ['nullable','string','max:30'],
            'registro_uf'      => ['nullable','string','size:2','regex:/^[A-Za-z]{2}$/'],
        ];

        $messages = [
            'usuario.regex'  => 'Use apenas letras, números, ponto (.), hífen (-), underline (_) e espaços simples (sem espaço no início/fim).',
            'usuario.unique' => 'Este Usuário já está em uso.',
        ];

        $role = (string) $request->input('role');

        if ($role === 'doctor') {
            $rules['especialidade_id'] = ['required','exists:especialidades,id'];
        }
        if ($this->needsCouncil($role)) {
            $rules['registro_numero'] = ['required','string','max:30'];
            $rules['registro_uf']     = ['required','string','size:2','regex:/^[A-Za-z]{2}$/'];
        }

        $data = $request->validate($rules, $messages);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if (!empty($data['registro_uf'])) {
            $data['registro_uf'] = strtoupper($data['registro_uf']);
        }

        if ($this->needsCouncil($role)) {
            $data['registro_tipo'] = strtoupper($data['registro_tipo'] ?? $this->councilCodeFor($role));
        } else {
            $data['especialidade_id'] = null;
            $data['registro_tipo'] = null;
            $data['registro_numero'] = null;
            $data['registro_uf'] = null;
        }

        $user->update($data);
        $user->syncRoles([$role]);

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
