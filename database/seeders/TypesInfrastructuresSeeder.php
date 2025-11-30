<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesInfrastructuresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['name' => 'school', 'label' => 'École'],
            ['name' => 'hospital', 'label' => 'Hôpital'],
            ['name' => 'clinic', 'label' => 'Centre de Santé'],
            ['name' => 'police', 'label' => 'Commissariat'],
            ['name' => 'townhall', 'label' => 'Mairie'],
            ['name' => 'government', 'label' => 'Administration'],
            ['name' => 'university', 'label' => 'Université'],
        ];

        foreach ($types as $type) {
            DB::table('types_infrastructures')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'name' => $type['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $count = DB::table('types_infrastructures')->count();
        $this->command->info("✅ {$count} types d'infrastructures créés/mis à jour avec succès !");
    }
 
}
