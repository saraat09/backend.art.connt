<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ArtisanProfile;
use App\Models\ArtisanService;
use App\Models\Message;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ══ Artisans ══════════════════════════ */
        $artisansData = [
            [
                'user' => [
                    'name'  => 'Mohamed Alami',
                    'email' => 'artisan1@test.com',
                    'phone' => '0661234567',
                    'city'  => 'Casablanca',
                ],
                'profile' => [
                    'trade'       => 'Électricien',
                    'description' => "Électricien diplômé avec 12 ans d'expérience. Intervention rapide sur tout Casablanca. Devis gratuit.",
                    'location'    => 'Casablanca, Maarif',
                    'available'   => true,
                    'rating'      => 4.9,
                    'reviews_count' => 48,
                ],
                'services' => [
                    ['service_name' => 'Installation électrique',  'price_from' => 300],
                    ['service_name' => 'Réparation tableau',        'price_from' => 200],
                    ['service_name' => 'Dépannage urgent',          'price_from' => 150],
                    ['service_name' => 'Mise aux normes',           'price_from' => 500],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Fatima Zahra Benali',
                    'email' => 'artisan2@test.com',
                    'phone' => '0672345678',
                    'city'  => 'Rabat',
                ],
                'profile' => [
                    'trade'       => 'Plombier',
                    'description' => "Plombière certifiée, disponible 7j/7. Intervention en urgence. Devis gratuit sous 24h.",
                    'location'    => 'Rabat, Agdal',
                    'available'   => true,
                    'rating'      => 4.8,
                    'reviews_count' => 34,
                ],
                'services' => [
                    ['service_name' => 'Fuite eau',               'price_from' => 150],
                    ['service_name' => 'Installation sanitaire',  'price_from' => 400],
                    ['service_name' => 'Chauffe-eau',             'price_from' => 350],
                    ['service_name' => 'Débouchage canalisation', 'price_from' => 200],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Youssef Cherkaoui',
                    'email' => 'artisan3@test.com',
                    'phone' => '0683456789',
                    'city'  => 'Marrakech',
                ],
                'profile' => [
                    'trade'       => 'Menuisier',
                    'description' => "Menuisier artisan spécialisé dans le bois massif et les matériaux nobles. 20 ans de métier.",
                    'location'    => 'Marrakech, Guéliz',
                    'available'   => true,
                    'rating'      => 4.7,
                    'reviews_count' => 62,
                ],
                'services' => [
                    ['service_name' => 'Meubles sur mesure',  'price_from' => 800],
                    ['service_name' => 'Portes & fenêtres',   'price_from' => 600],
                    ['service_name' => 'Parquet',             'price_from' => 120],
                    ['service_name' => 'Cuisine équipée',     'price_from' => 5000],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Hassan Tazi',
                    'email' => 'artisan4@test.com',
                    'phone' => '0694567890',
                    'city'  => 'Casablanca',
                ],
                'profile' => [
                    'trade'       => 'Peintre',
                    'description' => "Peintre décorateur professionnel. Travail soigné et prix compétitifs. Devis en 48h.",
                    'location'    => 'Casablanca, Hay Hassani',
                    'available'   => false,
                    'rating'      => 4.6,
                    'reviews_count' => 29,
                ],
                'services' => [
                    ['service_name' => 'Peinture intérieure', 'price_from' => 25],   // par m²
                    ['service_name' => 'Ravalement façade',   'price_from' => 40],
                    ['service_name' => 'Décoration',          'price_from' => 200],
                    ['service_name' => 'Enduit plâtre',       'price_from' => 30],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Aicha Moussaoui',
                    'email' => 'artisan5@test.com',
                    'phone' => '0605678901',
                    'city'  => 'Fès',
                ],
                'profile' => [
                    'trade'       => 'Carreleur',
                    'description' => "Spécialiste carrelage et revêtements de sol. Plus de 500 chantiers réalisés au Maroc.",
                    'location'    => 'Fès, Centre',
                    'available'   => true,
                    'rating'      => 4.8,
                    'reviews_count' => 51,
                ],
                'services' => [
                    ['service_name' => 'Pose carrelage',          'price_from' => 80],
                    ['service_name' => 'Faïence salle de bain',   'price_from' => 100],
                    ['service_name' => 'Terrasse',                'price_from' => 90],
                    ['service_name' => 'Marbre & pierres',        'price_from' => 150],
                ],
            ],
            [
                'user' => [
                    'name'  => 'Omar Benkirane',
                    'email' => 'artisan6@test.com',
                    'phone' => '0616789012',
                    'city'  => 'Tanger',
                ],
                'profile' => [
                    'trade'       => 'Serrurier',
                    'description' => "Serrurier urgentiste disponible 24h/24. Intervention en 30 minutes sur Tanger.",
                    'location'    => 'Tanger, Médina',
                    'available'   => true,
                    'rating'      => 4.9,
                    'reviews_count' => 77,
                ],
                'services' => [
                    ['service_name' => 'Ouverture de porte', 'price_from' => 150],
                    ['service_name' => 'Changement serrure', 'price_from' => 200],
                    ['service_name' => 'Blindage',           'price_from' => 800],
                    ['service_name' => 'Coffre-fort',        'price_from' => 300],
                ],
            ],
        ];

        $artisanUsers = [];

        foreach ($artisansData as $data) {
            $user = User::create([
                ...$data['user'],
                'password' => Hash::make('password'),
                'role'     => 'artisan',
            ]);

            $profile = ArtisanProfile::create([
                ...$data['profile'],
                'user_id' => $user->id,
            ]);

            foreach ($data['services'] as $svc) {
                ArtisanService::create([
                    ...$svc,
                    'artisan_profile_id' => $profile->id,
                ]);
            }

            $artisanUsers[] = $user;
        }

        /* ══ Client de test ════════════════════ */
        $client = User::create([
            'name'     => 'Client Test',
            'email'    => 'client@test.com',
            'password' => Hash::make('password'),
            'role'     => 'client',
            'phone'    => '0698765432',
            'city'     => 'Casablanca',
        ]);

        /* ══ Messages de démo ══════════════════ */
        // client → artisan1
        $m1 = Message::create([
            'sender_id'   => $client->id,
            'receiver_id' => $artisanUsers[0]->id,
            'body'        => "Bonjour, j'ai besoin d'un électricien pour refaire le tableau électrique. Êtes-vous disponible cette semaine ?",
            'read'        => true,
        ]);
        $m2 = Message::create([
            'sender_id'   => $artisanUsers[0]->id,
            'receiver_id' => $client->id,
            'body'        => "Bonjour ! Oui je suis disponible. Pouvez-vous me donner votre adresse exacte ?",
            'read'        => false,
        ]);
        $m3 = Message::create([
            'sender_id'   => $client->id,
            'receiver_id' => $artisanUsers[0]->id,
            'body'        => "Je suis au 12, Rue Mohamed V, Agdal. Appartement au 3ème étage.",
            'read'        => true,
        ]);
        $m4 = Message::create([
            'sender_id'   => $artisanUsers[0]->id,
            'receiver_id' => $client->id,
            'body'        => "Parfait, je peux passer mercredi matin vers 10h. Comptez entre 800 et 1200 MAD selon l'état du tableau.",
            'read'        => false,
        ]);

        // client → artisan2
        Message::create([
            'sender_id'   => $client->id,
            'receiver_id' => $artisanUsers[1]->id,
            'body'        => "Bonjour, j'ai une fuite sous l'évier. Urgence ! Pouvez-vous intervenir aujourd'hui ?",
            'read'        => true,
        ]);
        Message::create([
            'sender_id'   => $artisanUsers[1]->id,
            'receiver_id' => $client->id,
            'body'        => "Oui, je peux être chez vous dans 1 heure. Partagez-moi votre adresse.",
            'read'        => false,
        ]);

        /* ══ Notifications de démo ═════════════ */
        // Pour l'artisan1
        Notification::create([
            'user_id' => $artisanUsers[0]->id,
            'type'    => 'new_message',
            'data'    => [
                'from'       => $client->name,
                'from_id'    => $client->id,
                'message'    => "Bonjour, j'ai besoin d'un électricien pour refaire le tableau",
                'message_id' => $m1->id,
            ],
            'read' => false,
        ]);
        Notification::create([
            'user_id' => $artisanUsers[0]->id,
            'type'    => 'new_message',
            'data'    => [
                'from'       => $client->name,
                'from_id'    => $client->id,
                'message'    => "Je suis au 12, Rue Mohamed V, Agdal",
                'message_id' => $m3->id,
            ],
            'read' => false,
        ]);

        // Pour le client
        Notification::create([
            'user_id' => $client->id,
            'type'    => 'new_message',
            'data'    => [
                'from'       => $artisanUsers[0]->name,
                'from_id'    => $artisanUsers[0]->id,
                'message'    => "Bonjour ! Oui je suis disponible.",
                'message_id' => $m2->id,
            ],
            'read' => false,
        ]);
        Notification::create([
            'user_id' => $client->id,
            'type'    => 'new_message',
            'data'    => [
                'from'       => $artisanUsers[0]->name,
                'from_id'    => $artisanUsers[0]->id,
                'message'    => "Parfait, je peux passer mercredi matin vers 10h",
                'message_id' => $m4->id,
            ],
            'read' => false,
        ]);

        $this->command->info('✅ Seed terminé — 6 artisans + 1 client + messages + notifications créés');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Artisan 1', 'artisan1@test.com', 'password'],
                ['Artisan 2', 'artisan2@test.com', 'password'],
                ['Artisan 3', 'artisan3@test.com', 'password'],
                ['Artisan 4', 'artisan4@test.com', 'password'],
                ['Artisan 5', 'artisan5@test.com', 'password'],
                ['Artisan 6', 'artisan6@test.com', 'password'],
                ['Client',    'client@test.com',   'password'],
            ]
        );
    }
}
