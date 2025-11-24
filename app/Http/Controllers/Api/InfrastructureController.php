<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfrastructureController extends Controller
{
    public function index(Request $request)
    {
        // Base de la requête
        $query = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->select(
                'infrastructures.id',
                'infrastructures.name',
                'infrastructures.level',
                'infrastructures.operator',
                'types_infrastructures.name as type',
                // On demande à PostGIS de nous rendre la géométrie en GeoJSON
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry')
            );

        // Filtre simple par niveau (ex: ?level=primaire)
        if ($request->has('level')) {
            $query->where('infrastructures.level', $request->level);
        }

        // Filtre par opérateur (ex: ?operator=public)
        if ($request->has('operator')) {
            $query->where('infrastructures.operator', $request->operator);
        }

        $results = $query->get();

        // Reformatage en FeatureCollection (Standard GeoJSON)
        $features = $results->map(function($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geometry), // Décodage de la string GeoJSON de PostGIS
                'properties' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'level' => $item->level,
                    'operator' => $item->operator,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'count' => $features->count(),
            'features' => $features
        ]);
    }
}
