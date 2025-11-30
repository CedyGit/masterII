<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdministrationsSeeder extends Seeder
{
    public function run()
    {
        $townhallTypeId = DB::table('types_infrastructures')->where('name', 'townhall')->value('id');
        $govTypeId = DB::table('types_infrastructures')->where('name', 'government')->value('id');

        if (!$townhallTypeId || !$govTypeId) {
            $this->command->error("❌ Les types 'townhall' ou 'government' n'existent pas.");
            return;
        }

        $path = storage_path('app/administrations_madagascar.geojson');
        
        if (!File::exists($path)) {
            $this->command->error("❌ Fichier GeoJSON introuvable : $path");
            return;
        }

        $this->command->info("✅ Fichier GeoJSON trouvé");

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!$data || !isset($data['features'])) {
            $this->command->error("❌ Le fichier GeoJSON est invalide");
            return;
        }

        $totalFeatures = count($data['features']);
        $this->command->info("📊 Nombre d'administrations à importer : $totalFeatures");

        $imported = 0;
        $errors = 0;
        $bar = $this->command->getOutput()->createProgressBar($totalFeatures);
        $bar->start();

        foreach ($data['features'] as $feature) {
            try {
                $props = $feature['properties'];
                $geometry = $feature['geometry'];

                if (!$geometry || !isset($geometry['coordinates'])) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                $geojsonString = json_encode($geometry);
                
                // Déterminer le type
                $amenity = $props['amenity'] ?? null;
                $typeId = ($amenity === 'townhall') ? $townhallTypeId : $govTypeId;

                DB::table('infrastructures')->updateOrInsert(
                    ['osm_id' => $props['@id'] ?? null],
                    [
                        'type_infrastructure_id' => $typeId,
                        'name' => $props['name'] ?? 'Administration sans nom',
                        'level' => $props['admin_level'] ?? null,
                        'operator' => $props['operator'] ?? null,
                        'city' => $props['addr:city'] ?? null,
                        'geom' => DB::raw("ST_SetSRID(ST_GeomFromGeoJSON('$geojsonString'), 4326)"),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("✅ Importation terminée !");
        $this->command->table(
            ['Statistique', 'Valeur'],
            [
                ['Total', $totalFeatures],
                ['Importées', $imported],
                ['Erreurs', $errors],
            ]
        );
    }
}