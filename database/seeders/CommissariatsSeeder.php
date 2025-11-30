<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CommissariatsSeeder extends Seeder
{
    public function run()
    {
        $policeTypeId = DB::table('types_infrastructures')->where('name', 'police')->value('id');

        if (!$policeTypeId) {
            $this->command->error("❌ Le type 'police' n'existe pas.");
            return;
        }

        $path = storage_path('app/commissariats_madagascar.geojson');
        
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
        $this->command->info("📊 Nombre de commissariats à importer : $totalFeatures");

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

                DB::table('infrastructures')->updateOrInsert(
                    ['osm_id' => $props['@id'] ?? null],
                    [
                        'type_infrastructure_id' => $policeTypeId,
                        'name' => $props['name'] ?? 'Commissariat sans nom',
                        'level' => null,
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


