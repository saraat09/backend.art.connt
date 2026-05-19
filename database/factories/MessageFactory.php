<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'sender_id'   => 1,
            'receiver_id' => 2,
            'body'        => $this->faker->sentence(rand(5, 20)),
            'read'        => $this->faker->boolean(60),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn(array $attrs) => ['read' => false]);
    }
}
