<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Desempleado;
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Mail\RecoverEmail;
use App\Mail\PreRegisterEmail;


class AuthController extends Controller
{
    //

    public function testAuth (){
        return response()->json(['message' => 'Testiado'], 200);
    }


    public function register(Request $request)
    {
        // Log::error('Verifica si guarda errores de registro de usuario en logs ');
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if($validator->fails()){
            Log::error('Error de validación en registro de usuario: ' . json_encode($validator->errors()->all())  );
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ],422);// 422 (Unprocessable Entity) para errores de validación
        }

        try {

            $verificationCode = rand(100000, 999999);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'verificationCode' => $verificationCode,
                'created_at' => now()
            ]);

            $response = [];
            $response["token"] = $user->createToken($user->email)->plainTextToken;
            $response["email"] = $user->email;


            Mail::to($user->email)->send(new VerificationEmail($user, $verificationCode));

            Log::info("Se creo correctamente y se envio el correo de verificacion");
            return response()->json([
                "StatusCode" => 201,
                "ReasonPhrase" => "Usuario Registrado",
                'Message' => 'Usuario registrado correctamente',
                "Data" => $response
            ], 201); // 201 (Created) para registro exitoso

        } catch (QueryException $e) {

            if ($e->getCode() === '23000') { // Código de error para duplicado en MySQL

                 Log::error('Error de duplicado en registro de usuario: ' . $e->getMessage() );
                return response()->json([
                    "StatusCode" => 409,
                    "ReasonPhrase" => "El email ya está registrado.",
                    "Message" => "Email duplicado."
                ], 409);// 409 (Conflict) para duplicado
            }

            Log::error('Error de base de datos en registro de usuario: ' . $e->getMessage());
            // Manejar otros errores de base de datos si es necesario
            return response()->json([
                "StatusCode" => 500,
                "ReasonPhrase" => "Error interno del servidor.",
                "Message" => "Ocurrió un error al registrar el usuario."
            ], 500); // 500 (Internal Server Error) para otros errores
        } catch (Throwable $e) {
            Log::error('Error al enviar el correo de verificacion: ' . $e->getMessage());

            return response()->json([
                "StatusCode" => 500,
                "ReasonPhrase" => "Error al enviar correo.",
                "Message" => "El usuario se creó, pero no se pudo enviar el correo de verificación."
            ], 500);
        }

    }

    public function preRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users,email",
        ]);

        if($validator->fails()){
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ],422);
        }

        try {
            $temporaryPassword = $this->generateRandomPassword(10);
            $verificationCode = rand(100000, 999999);

            $user = User::create([
                'email' => $request->email,
                'rol' => 'admin',
                'password' => Hash::make($temporaryPassword),
                'verificationCode' => $verificationCode,
                'created_at' => now()
            ]);

            Mail::to($user->email)->send(new PreRegisterEmail($user, $temporaryPassword, $verificationCode));

            return response()->json([
                "StatusCode" => 201,
                "ReasonPhrase" => "Pre-registro creado",
                "Message" => "Administrador pre-registrado correctamente. Se ha enviado la contraseña temporal y el código de verificación al correo.",
                "Data" => [
                    "IDUsuario" => $user->IDUsuario,
                    "email" => $user->email,
                    "rol" => "Administrador"
                ]
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error de base de datos en pre-registro de administrador: ' . $e->getMessage());

            return response()->json([
                "StatusCode" => 500,
                "ReasonPhrase" => "Error interno del servidor.",
                "Message" => "Ocurrió un error al pre-registrar el administrador."
            ], 500);
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if($validator->fails()){
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ],422);// 422 (Unprocessable Entity) para errores de validación
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();


            $logged = null;
            $rol = null;

            if ($user->rol === 'admin') {
                $logged = Administrador::where('IDUsuario', $user->IDUsuario)->first();
                $rol = "Administrador";
            }

            if (!$logged) {
                $logged = Empresa::where('IDUsuario', $user->IDUsuario)->first();
                $rol = "Empresa";
            }
            if (!$logged) {
                $logged = Desempleado::where('IDUsuario', $user->IDUsuario)->first();
                $rol = "Usuario";
            }



            $response = [];
            $response["token"] = $user->createToken($user->email)->plainTextToken;
            $response["profile"] = $logged;
            $response["rol"] = $rol;

            return response()->json([
                "StatusCode" => 200,
                "ReasonPhrase" => "Usuario Logiado",
                'Message' => 'Usuario logeado correctamente',
                "Data" => $response
            ]);
        }

        // Verificar si el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "StatusCode" => 404,
                "ReasonPhrase" => "Error de credenciales",
                'Message' => 'El correo electrónico no existe.',
                "Data" => null
            ]);
        }

        // Verificar si la contraseña es incorrecta
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "StatusCode" => 401,
                "ReasonPhrase" => "Error de credenciales",
                'Message' => 'Contraseña incorrecta.',
                "Data" => null
            ]);
        }

        return response()->json([
            "StatusCode" => 404,
            "ReasonPhrase" => "Error",
            'Message' => 'Las credenciales no coinciden.',
            "Data" => null
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "code" => "required",
        ]);

        if($validator->fails()){
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "Faltan campos",
                "Message" => $validator->errors()->all()
            ],422);// 422 (Unprocessable Entity) para errores de validación
        }

         // Buscar usuario por email
        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json([
                "StatusCode" => 404,
                "ReasonPhrase" => "User Not Found",
                "Message" => "No se encontró un usuario con ese email."
            ], 404);
        }

        // Verificar si el código coincide
        if ($user->verificationCode !== $request->code) {
            return response()->json([
                "StatusCode" => 400,
                "ReasonPhrase" => "Invalid Code",
                "Message" => "El código ingresado es incorrecto."
            ], 400);
        }

        // Marcar al usuario como verificado
        $user->email_verified_at = now();
        $user->verificationCode = null; // Eliminar el código después de usarlo
        $user->save();

        return response()->json([
            "StatusCode" => 200,
            "ReasonPhrase" => "Success",
            "Message" => "Código verificado correctamente. Email confirmado."
        ], 200);

    }

    public function recover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
        ]);

        if($validator->fails()){
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "Se requiere un email valido",
                "Message" => $validator->errors()->all()
            ],422);// 422 (Unprocessable Entity) para errores de validación
        }

         // Buscar usuario por email
        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json([
                "StatusCode" => 404,
                "ReasonPhrase" => "User Not Found",
                "Message" => "No se encontró un usuario con ese email."
            ], 404);
        }

        // Generar nueva contraseña
        $newPassword = $this->generateRandomPassword();

        // Actualizar contraseña del usuario en la base de datos
        $user->password = Hash::make($newPassword);
        $user->save();

        Mail::to($user->email)->send(new RecoverEmail($user, $newPassword));

        return response()->json([
            "StatusCode" => 200,
            "ReasonPhrase" => "Contraseña recuperada",
            "Message" => "Se ha enviado una nueva contraseña al correo indicado."
        ], 200);

    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "current_password" => "required",
            "password" => "required|string|min:6|confirmed",
        ]);

        if($validator->fails()){
            return response()->json([
                "StatusCode" => 422,
                "ReasonPhrase" => "validation errors.",
                "Message" => $validator->errors()->all()
            ],422);
        }

        $user = $request->user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                "StatusCode" => 401,
                "ReasonPhrase" => "Contraseña actual incorrecta",
                "Message" => "La contraseña actual no coincide con la cuenta autenticada."
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            "StatusCode" => 200,
            "ReasonPhrase" => "Contraseña actualizada",
            "Message" => "La contraseña se ha actualizado correctamente."
        ], 200);
    }

    function generateRandomPassword($length = 6) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $hasLowercase = false;
        $hasUppercase = false;
        $hasNumber = false;

        while (strlen($password) < $length || !$hasLowercase || !$hasUppercase || !$hasNumber) {
            $password = '';
            $hasLowercase = false;
            $hasUppercase = false;
            $hasNumber = false;

            for ($i = 0; $i < $length; $i++) {
                $char = $characters[rand(0, strlen($characters) - 1)];
                $password .= $char;

                if (ctype_lower($char)) $hasLowercase = true;
                if (ctype_upper($char)) $hasUppercase = true;
                if (is_numeric($char)) $hasNumber = true;
            }
        }

        return $password;
    }

    public function listGroupUser()
    {
        $userId = auth()->id();
        $user = User::findOrFail($userId)->load('grupos'); // Asumiendo relación 'grupos' en el modelo User

        return response()->json([
            'StatusCode' => 200,
            'ReasonPhrase' => 'Grupos del usuario listados correctamente.',
            'data' => $user->grupos
        ], 200);
    }

    public function userID($idUsuario)
    {

        $user = Empresa::where('IDUsuario', $idUsuario)->first();
        $rol = "Empresa";
        if (!$user) {
            $user = Desempleado::where('IDUsuario', $idUsuario)->first();
            $rol = "Desempleado";
        }
        if (!$user) {
            $user = Administrador::where('IDUsuario', $idUsuario)->first();
            $rol = "Administrador";
        }

        if (!$user) {
            return response()->json([
                "StatusCode" => 404,
                "ReasonPhrase" => "Error de USUARIO",
                'Message' => 'El usuario no existe.',
                "Data" => null
            ]);
        }

        return response()->json([
            "StatusCode" => 200,
            "ReasonPhrase" => "Usuario encontrado",
            'Message' => 'Usuario encontrado correctamente',
            "Data" => $user,
            "Rol"=>$rol
        ]);

    }

    public function userRol()
    {

        $idUsuario = auth()->id();
        $user = Empresa::where('IDUsuario', $idUsuario)->first();
        $rol = "Empresa";
        if (!$user) {
            $user = Desempleado::where('IDUsuario', $idUsuario)->first();
            $rol = "Desempleado";
        }
        if (!$user) {
            $user = Administrador::where('IDUsuario', $idUsuario)->first();
            $rol = "Administrador";
        }

        if (!$user) {
            return response()->json([
                "StatusCode" => 404,
                "ReasonPhrase" => "Error de USUARIO",
                'Message' => 'El usuario no existe.',
                "Data" => null
            ]);
        }

        return response()->json([
            "StatusCode" => 200,
            "ReasonPhrase" => "Usuario encontrado",
            'Message' => 'Usuario encontrado correctamente',
            "Data" => $user,
            "Rol"=>$rol
        ]);

    }


}
