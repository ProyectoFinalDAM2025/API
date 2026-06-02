<?php

namespace App\Http\Controllers\API;

use App\Models\Administrador;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdministradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $administradores = Administrador::with('user:IDUsuario,email,rol,email_verified_at')->get();

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Administradores encontrados correctamente',
            'Message' => 'Los administradores han sido encontrados con exito.',
            'Data' => $administradores
        ], 200);
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
        $validator = Validator::make($request->all(), [
            'IDUsuario' => 'required|integer|exists:users,IDUsuario|unique:administradors,IDUsuario',
            'Nombre' => 'required|string|max:255',
            'Apellido' => 'required|string|max:255',
            'FotoPerfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'Activo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ], 422);
        }

        $authUser = $request->user();

        if ((int) $request->input('IDUsuario') !== (int) $authUser->IDUsuario) {
            return response()->json([
                "StatusCode" => 403,
                "ReasonPhrase" => "Acceso no autorizado.",
                "Message" => "No puedes crear un perfil administrador para otro usuario."
            ], 403);
        }

        if (!$authUser->email_verified_at) {
            return response()->json([
                "StatusCode" => 403,
                "ReasonPhrase" => "Email no verificado.",
                "Message" => "Debes verificar el email antes de crear un perfil administrador."
            ], 403);
        }

        if ($authUser->empresa || $authUser->desempleado) {
            return response()->json([
                "StatusCode" => 409,
                "ReasonPhrase" => "Perfil existente.",
                "Message" => "Este usuario ya tiene un perfil de empresa o desempleado."
            ], 409);
        }

        try {
            $fotoPerfil = null;
            if ($request->hasFile('FotoPerfil')) {
                $fotoPerfil = 'storage/' . $request->file('FotoPerfil')->store('administradores', 'public');
            }

            $administrador = Administrador::create([
                'IDUsuario' => $authUser->IDUsuario,
                'Nombre' => $request->input('Nombre'),
                'Apellido' => $request->input('Apellido'),
                'FotoPerfil' => $fotoPerfil,
                'Activo' => $request->input('Activo', true),
            ]);

            $authUser->rol = 'admin';
            $authUser->save();

            return response()->json([
                'StatusCode' => 200,
                'ReasonPhrase' => 'Administrador creado correctamente',
                'Message' => 'El perfil administrador ha sido registrado con exito.',
                'Data' => $administrador->load('user:IDUsuario,email,rol,email_verified_at')
            ], 200);
        } catch (QueryException $e) {
            Log::error('Error creando administrador: ' . $e->getMessage());

            return response()->json([
                "StatusCode" => 500,
                "ReasonPhrase" => "Error en la base de datos.",
                "Message" => "Ha ocurrido un problema al registrar el administrador.",
                "Exception" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Administrador $administrador)
    {
        $user = auth()->user();

        if ($user->rol !== 'admin' || $administrador->IDUsuario !== $user->IDUsuario) {
            return response()->json([
                "StatusCode" => 403,
                "ReasonPhrase" => "Acceso no autorizado.",
                "Message" => "No tienes permiso para acceder a este administrador."
            ], 403);
        }

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Administrador encontrado correctamente',
            'Message' => 'La informacion del administrador ha sido encontrada con exito.',
            'Data' => $administrador->load('user:IDUsuario,email,rol,email_verified_at')
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Administrador $administrador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Administrador $administrador)
    {
        $user = auth()->user();

        if ($user->rol !== 'admin') {
            return response()->json([
                "StatusCode" => 403,
                "ReasonPhrase" => "Acceso no autorizado.",
                "Message" => "No tienes permiso para modificar este administrador."
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'Nombre' => 'sometimes|required|string|max:255',
            'Apellido' => 'sometimes|required|string|max:255',
            'FotoPerfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'Activo' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ], 422);
        }

        if ($request->has('Nombre')) {
            $administrador->Nombre = $request->input('Nombre');
        }

        if ($request->has('Apellido')) {
            $administrador->Apellido = $request->input('Apellido');
        }

        if ($request->hasFile('FotoPerfil')) {
            if ($administrador->FotoPerfil) {
                Storage::disk('public')->delete(str_replace('storage/', '', $administrador->FotoPerfil));
            }

            $administrador->FotoPerfil = 'storage/' . $request->file('FotoPerfil')->store('administradores', 'public');
        }

        if ($request->has('Activo')) {
            $administrador->Activo = $request->boolean('Activo');
        }

        $administrador->save();

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Admin actualizado',
            'Message' => 'La actualización se ha realizado con éxito.',
            'Data' => $administrador->load('user:IDUsuario,email,rol,email_verified_at')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Administrador $administrador)
    {
        $user = auth()->user();

        if ($user->rol !== 'admin') {
            return response()->json([
                "StatusCode" => 403,
                "ReasonPhrase" => "Acceso no autorizado.",
                "Message" => "No tienes permiso para eliminar este administrador."
            ], 403);
        }

        if ($administrador->IDUsuario === $user->IDUsuario) {
            return response()->json([
                "StatusCode" => 409,
                "ReasonPhrase" => "Acción no permitida.",
                "Message" => "No puedes eliminar el administrador con la sesión iniciada."
            ], 409);
        }

        try {
            $usuarioAdministrador = $administrador->user;

            if ($administrador->FotoPerfil) {
                Storage::disk('public')->delete(str_replace('storage/', '', $administrador->FotoPerfil));
            }

            $administrador->delete();
            if ($usuarioAdministrador) {
                $usuarioAdministrador->delete();
            }

            return response()->json([
                "StatusCode" => 200,
                "ReasonPhrase" => "Administrador eliminado correctamente.",
                "Message" => "El administrador ha sido eliminado con exito."
            ], 200);
        } catch (QueryException $e) {
            Log::error('Error eliminando administrador: ' . $e->getMessage());

            return response()->json([
                "StatusCode" => 500,
                "ReasonPhrase" => "Error interno del servidor.",
                "Message" => "Ocurrio un error al intentar eliminar el administrador."
            ], 500);
        }
    }
}
