<?php

namespace Database\Seeders;

use App\Models\Desempleado;
use App\Models\Empresa;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sector = Sector::firstOrCreate(
            ['Nombre' => 'Tecnologia'],
            ['Nombre' => 'Tecnologia']
        );

        $empresas = [
            [
                'email' => 'empresa1@officium.local',
                'NombreEmpresa' => 'NovaTech Solutions',
                'CIF' => 'B12345671',
                'Ubicacion' => 'Madrid',
                'SitioWeb' => 'https://novatech.local',
            ],
            [
                'email' => 'empresa2@officium.local',
                'NombreEmpresa' => 'Innova Gestion',
                'CIF' => 'B12345672',
                'Ubicacion' => 'Barcelona',
                'SitioWeb' => 'https://innovagestion.local',
            ],
            [
                'email' => 'empresa3@officium.local',
                'NombreEmpresa' => 'DataBridge Labs',
                'CIF' => 'B12345673',
                'Ubicacion' => 'Valencia',
                'SitioWeb' => 'https://databridge.local',
            ],
            [
                'email' => 'empresaDelete@officium.local',
                'NombreEmpresa' => 'Empresa Delete Demo',
                'CIF' => 'B12345674',
                'Ubicacion' => 'Madrid',
                'SitioWeb' => 'https://empresa-delete-demo.local',
            ],
        ];

        foreach ($empresas as $empresaData) {
            $user = $this->createVerifiedUser($empresaData['email'], 'empresa');

            Empresa::updateOrCreate(
                ['IDUsuario' => $user->IDUsuario],
                [
                    'NombreEmpresa' => $empresaData['NombreEmpresa'],
                    'CIF' => $empresaData['CIF'],
                    'IDSector' => $sector->IDSector,
                    'Ubicacion' => $empresaData['Ubicacion'],
                    'SitioWeb' => $empresaData['SitioWeb'],
                    'Foto' => 'assets/default-company.png',
                ]
            );
        }

        $desempleados = [
            [
                'email' => 'desempleado1@officium.local',
                'Nombre' => 'Laura',
                'Apellido' => 'Martinez',
                'DNI' => '12345678A',
                'Porfolios' => 'https://portfolio-laura.local',
                'Disponibilidad' => 'Tiempo completo',
                'Ubicacion' => 'Sevilla',
            ],
            [
                'email' => 'desempleado2@officium.local',
                'Nombre' => 'Carlos',
                'Apellido' => 'Ruiz',
                'DNI' => '87654321B',
                'Porfolios' => 'https://portfolio-carlos.local',
                'Disponibilidad' => 'Freelance',
                'Ubicacion' => 'Malaga',
            ],
            [
                'email' => 'desempleadoDelete@officium.local',
                'Nombre' => 'Desempleado',
                'Apellido' => 'Delete Demo',
                'DNI' => '11223344C',
                'Porfolios' => 'https://desempleado-delete-demo.local',
                'Disponibilidad' => 'Temporal',
                'Ubicacion' => 'Madrid',
            ],
        ];

        foreach ($desempleados as $desempleadoData) {
            $user = $this->createVerifiedUser($desempleadoData['email'], 'usuario');

            Desempleado::updateOrCreate(
                ['IDUsuario' => $user->IDUsuario],
                [
                    'Nombre' => $desempleadoData['Nombre'],
                    'Apellido' => $desempleadoData['Apellido'],
                    'DNI' => $desempleadoData['DNI'],
                    'Porfolios' => $desempleadoData['Porfolios'],
                    'Disponibilidad' => $desempleadoData['Disponibilidad'],
                    'Ubicacion' => $desempleadoData['Ubicacion'],
                    'Foto' => 'assets/default-user.png',
                ]
            );
        }
    }

    private function createVerifiedUser(string $email, string $rol): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make('Aa1'),
                'rol' => $rol,
                'email_verified_at' => now(),
                'verificationCode' => null,
                'codeExpiresAt' => null,
            ]
        );
    }
}
