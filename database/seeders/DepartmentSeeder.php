<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['Alta Verapaz', 15.4697, -90.3723], ['Baja Verapaz', 15.1028, -90.3181],
            ['Chimaltenango', 14.6611, -90.8194], ['Chiquimula', 14.7972, -89.5448],
            ['El Progreso', 14.8538, -90.0649], ['Escuintla', 14.3009, -90.7850],
            ['Guatemala', 14.6349, -90.5069], ['Huehuetenango', 15.3192, -91.4706],
            ['Izabal', 15.7322, -88.5945], ['Jalapa', 14.6347, -89.9886],
            ['Jutiapa', 14.2919, -89.8958], ['Peten', 16.9297, -89.8917],
            ['Quetzaltenango', 14.8347, -91.5181], ['Quiche', 15.0306, -91.1481],
            ['Retalhuleu', 14.5349, -91.6770], ['Sacatepequez', 14.5586, -90.7339],
            ['San Marcos', 14.9639, -91.7944], ['Santa Rosa', 14.2769, -90.2993],
            ['Solola', 14.7730, -91.1836], ['Suchitepequez', 14.5347, -91.5038],
            ['Totonicapan', 14.9117, -91.3611], ['Zacapa', 14.9722, -89.5306],
        ];

        foreach ($departments as [$name, $latitude, $longitude]) {
            DB::table('departments')->updateOrInsert(
                ['slug' => Str::slug($name)],
                compact('name', 'latitude', 'longitude') + ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
