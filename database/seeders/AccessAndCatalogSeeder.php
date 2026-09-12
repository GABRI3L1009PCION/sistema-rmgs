<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccessAndCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['Administrador', 'admin', ['*']],
            ['Analista', 'analyst', ['dashboard.view', 'reports.manage', 'projections.view']],
            ['Tecnico', 'technician', ['farms.view', 'panels.manage', 'alerts.manage', 'maintenance.manage']],
            ['Visualizador', 'viewer', ['dashboard.view', 'reports.view', 'map.view']],
            ['Invitado', 'guest', ['dashboard.view']],
        ];
        foreach ($roles as [$name, $slug, $permissions]) {
            DB::table('roles')->updateOrInsert(['slug' => $slug], [
                'name' => $name, 'description' => "Rol de {$name} del sistema RMGS.",
                'permissions' => json_encode($permissions), 'is_system' => true,
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }

        $users = [
            ['Gabriel Admin', 'admin@rmgs.test', 'admin', 'active'],
            ['Maria Lopez', 'analista@rmgs.test', 'analyst', 'active'],
            ['Juan Rodriguez', 'tecnico@rmgs.test', 'technician', 'active'],
            ['Ana Martinez', 'consulta@rmgs.test', 'viewer', 'active'],
            ['Sofia Herrera', 'invitado@rmgs.test', 'guest', 'pending'],
        ];
        foreach ($users as [$name, $email, $role, $status]) {
            DB::table('users')->updateOrInsert(['email' => $email], [
                'role_id' => DB::table('roles')->where('slug', $role)->value('id'),
                'name' => $name, 'password' => Hash::make('password'), 'status' => $status,
                'email_verified_at' => $status === 'active' ? now() : null,
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }
        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('notification_preferences')->updateOrInsert(['user_id' => $userId], [
                'email_notifications' => true, 'push_notifications' => true,
                'weekly_reports' => true, 'critical_alerts' => true,
                'channels' => json_encode(['email', 'browser']),
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }

        $models = [
            ['SolarMax', 'SM-450 Mono', .450, 'Monocristalino', 20.70, '1909 x 1134 x 30 mm', 22.0, 25],
            ['HelioTech', 'HT-550 Bifacial', .550, 'Monocristalino bifacial', 21.30, '2278 x 1134 x 35 mm', 31.5, 30],
            ['Quetzal Solar', 'QS-410 Poly', .410, 'Policristalino', 19.80, '1722 x 1134 x 30 mm', 21.0, 20],
            ['Jinko Solar', 'JKM550M-72HL4', .550, 'Monocristalino PERC', 21.29, '2278 x 1134 x 35 mm', 28.6, 25],
            ['LONGi', 'LR5-54SHPH-430M', .430, 'Monocristalino', 22.00, '1722 x 1134 x 30 mm', 20.8, 25],
            ['Canadian Solar', 'CS6W-540MS', .540, 'Monocristalino', 20.90, '2261 x 1134 x 35 mm', 27.8, 25],
            ['Trina Solar', 'TSM-NEG19RC.20', .580, 'N-type TOPCon', 22.50, '2384 x 1134 x 30 mm', 28.8, 30],
            ['JA Solar', 'JAM72S30-545', .545, 'Monocristalino PERC', 21.10, '2278 x 1134 x 35 mm', 28.6, 25],
        ];
        foreach ($models as [$brand, $model, $power, $technology, $efficiency, $dimensions, $weight, $warranty]) {
            DB::table('panel_models')->updateOrInsert(compact('brand', 'model'), [
                'nominal_power_kw' => $power, 'technology' => $technology,
                'panel_type' => 'Modulo fotovoltaico', 'efficiency_percent' => $efficiency,
                'dimensions' => $dimensions, 'weight_kg' => $weight,
                'warranty_years' => $warranty, 'status' => 'active',
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }
    }
}
