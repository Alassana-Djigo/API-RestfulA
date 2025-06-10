<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validar os dados recebidos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Criar o usuário no banco
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Criptografa a senha
        ]);

        // Gerar o token para o usuário
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retornar resposta JSON
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201); // HTTP 201 Created
    }
    public function login(Request $request)
    {
    // Validar os dados recebidos
    $validated = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // Buscar o usuário pelo email
    $user = User::where('email', $validated['email'])->first();

    // Se usuário não encontrado OU senha incorreta
    if (! $user || ! \Illuminate\Support\Facades\Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Credenciais inválidas.'
        ], 401); // 401 Unauthorized
    }

    // Gerar um novo token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Retornar resposta JSON
    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
    }
    public function logout(Request $request)
    {
    // Pega o token atual do usuário e deleta (revoga)
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logout realizado com sucesso.'
    ]);
    }
}