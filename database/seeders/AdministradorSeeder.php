<?php

namespace Database\Seeders;

use App\Models\Administrador;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'officium.portarentur@gmail.com'],
            [
                'password' => Hash::make('Aa1'),
                'rol' => 'admin',
                'email_verified_at' => now(),
                'verificationCode' => null,
                'codeExpiresAt' => null,
            ]
        );

        Administrador::where('IDUsuario', '!=', $user->IDUsuario)->delete();

        Administrador::updateOrCreate(
            ['IDUsuario' => $user->IDUsuario],
            [
                'Nombre' => 'OFFICIUM',
                'Apellido' => 'Administrador',
                'FotoPerfil' => null,
                'Activo' => true,
            ]
        );

        User::where('rol', 'admin')
            ->where('IDUsuario', '!=', $user->IDUsuario)
            ->update(['rol' => 'usuario']);
    }
}
