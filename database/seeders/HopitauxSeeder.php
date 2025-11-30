<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HopitauxSeeder extends Seeder
{
    public function run()
    {
        // Récupérer l'ID du type "hospital"
        $hospitalTypeId = DB::table('types_infrastructures')->where('name', 'hospital')->value('id');
        $clinicTypeId = DB::table('types_infrastructures')->where('name', 'clinic')->value('id');

        if (!$hospitalTypeId) {
            $this->command->error("❌ Le type 'hospital' n'existe pas.");
            return;
        }

        // Lire le fichier GeoJSON
        $path = storage_path('app/hopitaux_madagascar.geojson');
        
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
        $this->command->info("📊 Nombre d'hôpitaux à importer : $totalFeatures");

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
                
                // Déterminer le type (hospital ou clinic)
                $amenity = $props['amenity'] ?? null;
                $typeId = ($amenity === 'clinic') ? $clinicTypeId : $hospitalTypeId;

                DB::table('infrastructures')->updateOrInsert(
                    ['osm_id' => $props['@id'] ?? null],
                    [
                        'type_infrastructure_id' => $typeId,
                        'name' => $props['name'] ?? 'Hôpital sans nom',
                        'level' => $props['healthcare'] ?? null,
                        'operator' => $props['operator'] ?? $props['operator:type'] ?? null,
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
                ['Total dans le fichier', $totalFeatures],
                ['Importées', $imported],
                ['Erreurs', $errors],
            ]
        );
    }
}