<?php

    namespace App\Http\Controllers;

use App\Models\Experiencia;
use App\Models\Usuario;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Validation\ValidationException;
    use Illuminate\Support\Facades\Hash;

    class UsuarioController extends Controller
    {
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            return Usuario::all();
        }

        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {
            try {
                $request->validate([
                    'nome' => 'required|string',
                    'email' => 'required|email|unique:usuarios,email|unique:empresas,email',
                    'senha' => 'required|string|min:8'
                ]);

                $usuario = new Usuario();
                $usuario->nome = $request->nome;
                $usuario->email = $request->email;
                $usuario->senha = Hash::make($request->senha);
                $usuario->save();

                return response()->json(['mensagem' => 'Usuario criado com sucesso', 'usuario' => $usuario], 200);
            }  catch (ValidationException $e) {
                $errors = $e->errors();
                $errorMessages = [];

                foreach ($errors as $field => $messages) {
                    foreach ($messages as $message) {
                        if ($message === 'validation.unique') {
                            return response()->json(['mensagem' => 'email ja cadastrado'], 422);
                        } else {
                            $errorMessages[] = ['field' => $field, 'mensagem' => $message];
                        }
                    }
                }

                return response()->json(['mensagem' => $errorMessages], 422);
            } catch (\Exception $e) {
                return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
            }
        }

        /**
 * Display the specified resource.
 */
    public function show(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['mensagem' => 'Token nao encontrado'], 401);
            }

            $userIdFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

            if (!$userIdFromToken) {
                return response()->json(['mensagem' => 'Token invalido'], 401);
            }

            $usuario = Usuario::with('competencias', 'experiencias')->where('id', $userIdFromToken)->first();

            if (!$usuario) {
                return response()->json(['mensagem' => 'Usuario nao encontrado'], 404);
            }

            $usuario->experiencias->each(function ($experiencia) {
                $experiencia->makeHidden(['id_candidato', 'created_at', 'updated_at']);
            });

            $competencias = $usuario->competencias->map(function ($competencia) {
                return $competencia->only('id', 'nome');
            });

            $data = [
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'tipo' => 'candidato',
                'competencias' => $competencias,
                'experiencia' => $usuario->experiencias
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }




        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $userIdFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

            if (!$userIdFromToken) {
                return response()->json(['mensagem' => 'Token inválido'], 401);
            }
            $usuario = Usuario::find($userIdFromToken['id']);

            $request->validate([
                'nome' => 'required|string',
                'email' => 'required|email',
                'senha' => 'nullable|string|min:8',
                'competencias' => 'nullable|array',
                'competencias.*.id' => 'required_with:competencias|exists:competencias,id',
                'experiencias' => 'nullable|array',
                'experiencias.*.nome_empresa' => 'required_with:experiencias|string',
                'experiencias.*.inicio' => 'required_with:experiencias|date',
                'experiencias.*.fim' => 'nullable|date',
                'experiencias.*.cargo' => 'required_with:experiencias|string',
                'experiencias.*.descricao' => 'nullable|string'
            ]);






            if (!$usuario) {
                return response()->json(['mensagem' => 'Usuário não encontrado'], 404);
            }

            if ($request->has('nome')) {
                $usuario->nome = $request->nome;
            }

            if ($request->has('email')) {
                $usuario->email = $request->email;
            }

            if ($request->has('senha')) {
                $usuario->senha = Hash::make($request->senha);
            }
            $usuario->save();

            if ($request->has('competencias')) {
                $competenciaIds = array_column($request->competencias, 'id');
                $usuario->competencias()->sync($competenciaIds);
            }

            if ($request->has('experiencia')) {
                $usuario->experiencias()->delete();

                foreach ($request->experiencia as $exp) {
                    $experiencia = new Experiencia();
                    $experiencia->nome_empresa = $exp['nome_empresa'];
                    $experiencia->inicio = $exp['inicio'];
                    $experiencia->fim = $exp['fim'] ?? null;
                    $experiencia->cargo = $exp['cargo'];
                    $experiencia->id_candidato = $usuario->id;
                    $experiencia->save();
                }
            }

            return response()->json(['mensagem' => 'Usuario atualizado com sucesso', 'usuario: ' => $usuario], 200);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = ['field' => $field, 'mensagem' => $message];
                }
            }
            info($e);
            return response()->json(['mensagem' => $errorMessages ], 422);

        } catch (\Exception $e) {
            //info($e);
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $userData = Cache::get(TOKEN_CACHE_PREFIX . $token);

            if (!isset($userData['id'])) {
                return response()->json(['mensagem' => 'Token invalido'], 401);
            }

            $userIdFromToken = $userData['id'];

            $usuario = Usuario::find($userIdFromToken);
            if (!$usuario) {
                return response()->json(['mensagem' => 'Usuario nao encontrado'], 404);
            }

            $usuario->forceDelete();
            Cache::forget(TOKEN_CACHE_PREFIX . $token);

            return response()->json(['mensagem' => 'Usuario excluido com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor: '.$e->getMessage()], 500);
        }
    }
    /**
     * Buscar perfis de usuário com base nas competências e experiência.
     */
        public function buscarPerfil(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['mensagem' => 'Token não encontrado'], 401);
            }

            $userData = Cache::get(TOKEN_CACHE_PREFIX . $token);

            if (!isset($userData['id'])) {
                return response()->json(['mensagem' => 'Token inválido'], 401);
            }

            $request->validate([
                'competencias' => 'required|array',
                'competencias.*' => 'exists:competencias,id'
            ]);

            $competenciaIds = $request->competencias;

            $usuarios = Usuario::whereHas('competencias', function ($query) use ($competenciaIds) {
                $query->whereIn('competencias.id', $competenciaIds);
            })
            ->with(['competencias' => function ($query) {
                $query->select('competencias.id', 'competencias.nome');
            }, 'experiencias'])
            ->get();

            if ($usuarios->isEmpty()) {
                return response()->json(['mensagem' => 'Nenhum usuário encontrado com os critérios especificados.'], 204);
            }

            $candidatos = $usuarios->map(function ($usuario) {
                $competencias = $usuario->competencias->map(function ($competencia) {
                    return $competencia->only('id', 'nome');
                });

                $experiencias = $usuario->experiencias->map(function ($experiencia) {
                    return $experiencia->only('nome_empresa', 'inicio', 'fim', 'cargo');
                });

                return [
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'tipo' => 'candidato',
                    'competencias' => $competencias,
                    'experiencia' => $experiencias
                ];
            });

            return response()->json(['candidatos' => $candidatos], 200);

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = ['field' => $field, 'mensagem' => $message];
                }
            }

            return response()->json(['mensagem' => $errorMessages], 422);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }



}
