<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(3),
            'date' => Carbon::now()->addDays(3),
            'description' => $this->faker->paragraph(),
            'max_participants' => 3,
            'location' => $this->faker->city(),
            'is_public' => true,
            'route_geometry' => $this->sampleRoute(),
        ];
    }

    protected function sampleRoute(): array
    {
        return [
            'waypoints' => [
                ['lat' => 47.4979, 'lng' => 19.0402],
                ['lat' => 47.506, 'lng' => 19.08],
            ],
            'coordinates' => [
                [47.4979, 19.0402],
                [47.506, 19.08],
            ],
            'summary' => [
                'totalDistance' => 5200,
                'totalTime' => 1800,
            ],
        ];
    }
}
