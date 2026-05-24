<?php

namespace Database\Factories;

use App\Models\ArtisanProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtisanProfileFactory extends Factory
{
    protected $model = ArtisanProfile::class;

    private array $trades = [
        'Électricien', 'Plombier', 'Menuisier', 'Peintre',
        'Carreleur', 'Serrurier', 'Maçon', 'Climatisation',
    ];

    private array $cities = [
        'Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger',
        'Agadir', 'Meknès', 'Oujda',
    ];

    public function definition(): array
    {
        $city = $this->faker->randomElement($this->cities);

        return [
            'trade'         => $this->faker->randomElement($this->trades),
            'description'   => $this->faker->paragraph(3),
            'location'      => $city . ', ' . $this->faker->streetName(),
            'available'     => $this->faker->boolean(80),
            'rating'        => $this->faker->randomFloat(1, 3.5, 5.0),
            'reviews_count' => $this->faker->numberBetween(0, 120),
        ];
    }
}
