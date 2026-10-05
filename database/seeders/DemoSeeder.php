<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException(
                'Os dados de demonstração só podem ser criados no ambiente local.'
            );
        }

        $this->call(DatabaseSeeder::class);

        $this->removerUsuariosDemonstracaoAntigos();

        $admin = User::firstOrNew([
            'email' => 'admin@central.test',
        ]);

        if (! $admin->exists) {
            $admin->name = 'Erick Oliveira';
            $admin->password = 'Central@123';
            $admin->role = 'admin';
            $admin->save();
        }
    }

    private function removerUsuariosDemonstracaoAntigos(): void
    {
        $usuarios = User::query()
            ->whereIn('email', [
                'tecnico@central.test',
                'cliente@central.test',
            ])
            ->get()
            ->keyBy('email');

        if ($usuarios->isEmpty()) {
            return;
        }

        $ids = $usuarios
            ->pluck('id')
            ->all();

        $clienteId = $usuarios
            ->get('cliente@central.test')
            ?->id;

        $tecnicoId = $usuarios
            ->get('tecnico@central.test')
            ?->id;

        DB::table('comments')
            ->whereIn('user_id', $ids)
            ->delete();

        DB::table('activities')
            ->whereIn('user_id', $ids)
            ->delete();

        DB::table('attachments')
            ->whereIn('user_id', $ids)
            ->delete();

        if ($tecnicoId) {
            DB::table('tickets')
                ->where('assigned_to', $tecnicoId)
                ->update([
                    'assigned_to' => null,
                ]);
        }

        if ($clienteId) {
            DB::table('tickets')
                ->where('user_id', $clienteId)
                ->delete();
        }

        User::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}
