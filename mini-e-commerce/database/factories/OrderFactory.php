<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentStatus = $this->faker->randomElement(['unpaid', 'paid']);
        
        // Ensure status matches payment status based on actual database constraints
        $status = match($paymentStatus) {
            'unpaid' => 'pending',
            'paid' => $this->faker->randomElement(['processing', 'completed']),
            default => 'pending'
        };
        
        return [
            'order_code' => 'ORD-' . strtoupper($this->faker->unique()->lexify('??????')),
            'user_id' => User::factory(),
            'total_price' => $this->faker->numberBetween(10000, 1000000),
            'payment_status' => $paymentStatus,
            'status' => $status,
        ];
    }

    /**
     * Indicate that the order is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'unpaid',
            'status' => 'cancelled',
        ]);
    }
}
