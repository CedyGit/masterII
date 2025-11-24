<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
   public function run()
    {
        // 1. Récupérer l'ID du type "school" que tu as créé
        $schoolTypeId = DB::table('types_infrastructures')->where('name', 'school')->value('id');

        if (!$schoolTypeId) {
            $this->command->error("Le type 'school' n'existe pas. Lance d'abord TypesInfrastructuresSeeder.");
            return;
        }

        // 2. Lire le fichier GeoJSON
        $path = storage_path('app/ecoles_madagascar.geojson'); // Vérifie que le fichier est bien là !
        if (!File::exists($path)) {
            $this->command->error("Fichier GeoJSON introuvable dans : $path");
            return;
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        $this->command->info("Importation des écoles en cours...");

        foreach ($data['features'] as $feature) {
            $props = $feature['properties'];
            $geometry = $feature['geometry'];

            // Convertir le tableau de géométrie en string JSON pour PostGIS
            $geojsonString = json_encode($geometry);

            DB::table('infrastructures')->updateOrInsert(
                ['osm_id' => $props['@id'] ?? null], // On évite les doublons basés sur l'ID OSM
                [
                    'type_infrastructure_id' => $schoolTypeId,
                    'name'     => $props['name'] ?? 'Nom inconnu',
                    'level'    => $props['school:MG'] ?? null,      // Mappe "school:MG" vers "level"
                    'operator' => $props['operator:type'] ?? null,  // Mappe "operator:type" vers "operator"
                    'city'     => $props['addr:city'] ?? null,
                    // FONCTION MAGIQUE POSTGIS : Transforme le GeoJSON en géométrie binaire
                    'geom'     => DB::raw("ST_SetSRID(ST_GeomFromGeoJSON('$geojsonString'), 4326)"),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info("Importation terminée !");
    }
}
