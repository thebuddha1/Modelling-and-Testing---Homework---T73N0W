<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create new favorite_places table (without user_id, with creator_id)
        Schema::create('favorite_places_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->text('description')->nullable();
        });

        // Create user_favorite_places junction table
        Schema::create('user_favorite_places', function (Blueprint $table) {
            $table->foreignId('favorite_place_id')->constrained('favorite_places_new')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->primary(['favorite_place_id', 'user_id']);
        });

        // Migrate existing data
        $oldFavorites = DB::table('favorite_places')->get();
        
        foreach ($oldFavorites as $old) {
            // Determine creator_id: if shared_by_id exists, that's the creator, otherwise the user_id
            $creatorId = $old->shared_by_id ?? $old->user_id;
            
            // Check if this place already exists for this creator
            $existingPlace = DB::table('favorite_places_new')
                ->where('creator_id', $creatorId)
                ->where('name', $old->name)
                ->where('lat', $old->lat)
                ->where('lng', $old->lng)
                ->first();
            
            if ($existingPlace) {
                // Place already exists, just add user relationship
                $placeId = $existingPlace->id;
            } else {
                // Create new place
                $placeId = DB::table('favorite_places_new')->insertGetId([
                    'creator_id' => $creatorId,
                    'name' => $old->name,
                    'lat' => $old->lat,
                    'lng' => $old->lng,
                    'description' => null,
                ]);
            }
            
            // Add user relationship
            DB::table('user_favorite_places')->insert([
                'favorite_place_id' => $placeId,
                'user_id' => $old->user_id,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);
        }

        // Drop old table
        Schema::dropIfExists('favorite_places');
        
        // Rename new table
        Schema::rename('favorite_places_new', 'favorite_places');
    }

    public function down(): void
    {
        // Recreate old structure
        Schema::create('favorite_places_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('shared_by_id')->nullable();
            $table->string('name');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        // Migrate data back
        $places = DB::table('favorite_places')->get();
        $userPlaces = DB::table('user_favorite_places')->get();
        
        foreach ($userPlaces as $up) {
            $place = $places->firstWhere('id', $up->favorite_place_id);
            if ($place) {
                $sharedById = ($place->creator_id != $up->user_id) ? $place->creator_id : null;
                
                DB::table('favorite_places_old')->insert([
                    'user_id' => $up->user_id,
                    'shared_by_id' => $sharedById,
                    'name' => $place->name,
                    'lat' => $place->lat,
                    'lng' => $place->lng,
                    'created_at' => $up->created_at,
                    'updated_at' => $up->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('user_favorite_places');
        Schema::dropIfExists('favorite_places');
        Schema::rename('favorite_places_old', 'favorite_places');
    }
};
