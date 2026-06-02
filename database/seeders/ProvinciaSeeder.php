<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Provincia;

class ProvinciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
              $provinces = [
            ['name' => 'Álava', 'INE' => '01'],
            ['name' => 'Albacete', 'INE' => '02'],
            ['name' => 'Alicante', 'INE' => '03'],
            ['name' => 'Almería', 'INE' => '04'],
            ['name' => 'Ávila', 'INE' => '05'],
            ['name' => 'Badajoz', 'INE' => '06'],
            ['name' => 'Baleares', 'INE' => '07'],
            ['name' => 'Barcelona', 'INE' => '08'],
            ['name' => 'Burgos', 'INE' => '09'],
            ['name' => 'Cáceres', 'INE' => '10'],
            ['name' => 'Cádiz', 'INE' => '11'],
            ['name' => 'Castellón', 'INE' => '12'],
            ['name' => 'Ciudad Real', 'INE' => '13'],
            ['name' => 'Córdoba', 'INE' => '14'],
            ['name' => 'A Coruña', 'INE' => '15'],
            ['name' => 'Cuenca', 'INE' => '16'],
            ['name' => 'Girona', 'INE' => '17'],
            ['name' => 'Granada', 'INE' => '18'],
            ['name' => 'Guadalajara', 'INE' => '19'],
            ['name' => 'Guipúzcoa', 'INE' => '20'],
            ['name' => 'Huelva', 'INE' => '21'],
            ['name' => 'Huesca', 'INE' => '22'],
            ['name' => 'Jaén', 'INE' => '23'],
            ['name' => 'León', 'INE' => '24'],
            ['name' => 'Lleida', 'INE' => '25'],
            ['name' => 'La Rioja', 'INE' => '26'],
            ['name' => 'Lugo', 'INE' => '27'],
            ['name' => 'Madrid', 'INE' => '28'],
            ['name' => 'Málaga', 'INE' => '29'],
            ['name' => 'Murcia', 'INE' => '30'],
            ['name' => 'Navarra', 'INE' => '31'],
            ['name' => 'Ourense', 'INE' => '32'],
            ['name' => 'Asturias', 'INE' => '33'],
            ['name' => 'Palencia', 'INE' => '34'],
            ['name' => 'Las Palmas', 'INE' => '35'],
            ['name' => 'Pontevedra', 'INE' => '36'],
            ['name' => 'Salamanca', 'INE' => '37'],
            ['name' => 'Santa Cruz de Tenerife', 'INE' => '38'],
            ['name' => 'Cantabria', 'INE' => '39'],
            ['name' => 'Segovia', 'INE' => '40'],
            ['name' => 'Sevilla', 'INE' => '41'],
            ['name' => 'Soria', 'INE' => '42'],
            ['name' => 'Tarragona', 'INE' => '43'],
            ['name' => 'Teruel', 'INE' => '44'],
            ['name' => 'Toledo', 'INE' => '45'],
            ['name' => 'Valencia', 'INE' => '46'],
            ['name' => 'Valladolid', 'INE' => '47'],
            ['name' => 'Vizcaya', 'INE' => '48'],
            ['name' => 'Zamora', 'INE' => '49'],
            ['name' => 'Zaragoza', 'INE' => '50'],
            ['name' => 'Ceuta', 'INE' => '51'],
            ['name' => 'Melilla', 'INE' => '52'],
        ];

        foreach ($provinces as $province) {
            Provincia::firstOrCreate(
                ['INE' => $province['INE']],
                ['name' => $province['name']]
            );
        }
    }
}
