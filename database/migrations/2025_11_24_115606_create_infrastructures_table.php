<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInfrastructuresTable extends Migration
{
    public function up()
    {
        Schema::create('infrastructures', function (Blueprint $table) {
            $table->id();
            
            // Clé étrangère vers types_infrastructures
            $table->foreignId('type_infrastructure_id')
                  ->constrained('types_infrastructures')
                  ->onDelete('cascade');

            $table->string('osm_id')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('level')->nullable();
            $table->string('operator')->nullable();
            $table->string('city')->nullable();
            
            $table->timestamps();
        });

        // Ajouter la colonne géométrique PostGIS APRÈS la création de la table
        // DB::statement('ALTER TABLE infrastructures ADD COLUMN geom geometry(Point, 4326)');
        DB::statement('ALTER TABLE infrastructures ADD COLUMN geom geometry(Geometry, 4326)');
        DB::statement('CREATE INDEX infrastructures_geom_gist ON infrastructures USING GIST (geom)');
    }

    public function down()
    {
        Schema::dropIfExists('infrastructures');
    }
}