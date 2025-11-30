<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfrastructureController extends Controller
{
    /**
     * Votre méthode index() originale - INCHANGÉE
     */
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
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry')
            );

        // AJOUT : Filtre par type (ex: ?type=school)
        if ($request->has('type') && $request->type != '') {
            $query->where('types_infrastructures.name', $request->type);
        }

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
                'geometry' => json_decode($item->geometry),
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

    /**
     * NOUVELLES MÉTHODES - Ajoutées en dessous
     */
    
    /**
     * Liste tous les types d'infrastructures
     */
    public function getTypes()
    {
        $types = DB::table('types_infrastructures')
            ->select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Infrastructures par type
     */
    public function byType($type)
    {
        $query = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->where('types_infrastructures.name', $type)
            ->select(
                'infrastructures.id',
                'infrastructures.name',
                'infrastructures.level',
                'infrastructures.operator',
                'infrastructures.city',
                'types_infrastructures.name as type',
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry')
            );

        $results = $query->get();

        $features = $results->map(function($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geometry),
                'properties' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'level' => $item->level,
                    'operator' => $item->operator,
                    'city' => $item->city,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'count' => $features->count(),
            'features' => $features
        ]);
    }

    /**
     * Infrastructures par ville
     */
    public function byCity($city)
    {
        $query = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->where('infrastructures.city', 'ILIKE', "%$city%")
            ->select(
                'infrastructures.id',
                'infrastructures.name',
                'infrastructures.level',
                'infrastructures.operator',
                'infrastructures.city',
                'types_infrastructures.name as type',
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry')
            );

        $results = $query->get();

        $features = $results->map(function($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geometry),
                'properties' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'level' => $item->level,
                    'operator' => $item->operator,
                    'city' => $item->city,
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'count' => $features->count(),
            'features' => $features
        ]);
    }

    /**
     * Recherche dans un rayon (nearby)
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'numeric|min:0.1|max:100'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->get('radius', 5) * 1000; // km vers mètres

        $results = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->select(
                'infrastructures.id',
                'infrastructures.name',
                'infrastructures.city',
                'types_infrastructures.name as type',
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry'),
                DB::raw("ST_Distance(infrastructures.geom::geography, ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography) as distance")
            )
            ->whereRaw("ST_DWithin(infrastructures.geom::geography, ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography, $radius)")
            ->orderBy('distance')
            ->get();

        $features = $results->map(function($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geometry),
                'properties' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'city' => $item->city,
                    'distance_km' => round($item->distance / 1000, 2),
                ]
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'count' => $features->count(),
            'center' => ['lat' => $lat, 'lng' => $lng],
            'radius_km' => $request->get('radius', 5),
            'features' => $features
        ]);
    }

    /**
     * Détails d'une infrastructure
     */
    public function show($id)
    {
        $infrastructure = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->where('infrastructures.id', $id)
            ->select(
                'infrastructures.*',
                'types_infrastructures.name as type',
                DB::raw('ST_AsGeoJSON(infrastructures.geom) as geometry'),
                DB::raw('ST_X(ST_Centroid(infrastructures.geom)) as longitude'),
                DB::raw('ST_Y(ST_Centroid(infrastructures.geom)) as latitude')
            )
            ->first();

        if (!$infrastructure) {
            return response()->json([
                'success' => false,
                'message' => 'Infrastructure non trouvée'
            ], 404);
        }

        $infrastructure->geometry = json_decode($infrastructure->geometry);

        return response()->json([
            'success' => true,
            'data' => $infrastructure
        ]);
    }

    /**
     * Statistiques globales
     */
    public function stats()
    {
        $stats = DB::table('infrastructures')
            ->join('types_infrastructures', 'infrastructures.type_infrastructure_id', '=', 'types_infrastructures.id')
            ->select(
                'types_infrastructures.name as type',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('types_infrastructures.name')
            ->get();

        $total = DB::table('infrastructures')->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'by_type' => $stats
        ]);
    }
}