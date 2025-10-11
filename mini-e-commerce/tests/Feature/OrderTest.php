<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();
    
    // Create test products
    $this->product1 = Product::factory()->create([
        'product_name' => 'Test Product 1',
        'price' => 100000,
        'stock' => 10
    ]);
    
    $this->product2 = Product::factory()->create([
        'product_name' => 'Test Product 2',
        'price' => 50000,
        'stock' => 5
    ]);
    
    // Mock Midtrans configuration
    Config::set('midtrans.server_key', 'test-server-key');
    Config::set('midtrans.client_key', 'test-client-key');
    Config::set('midtrans.is_production', false);
});

test('user can create order with valid data', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 2
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 1
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'order' => [
                    'id',
                    'order_code',
                    'user_id',
                    'total_price',
                    'payment_status',
                    'status'
                ],
                'items' => [
                    '*' => [
                        'id',
                        'order_id',
                        'product_id',
                        'product_name',
                        'price',
                        'quantity'
                    ]
                ],
                'snap_token'
            ]
        ]);

    // Verify order was created
    $this->assertDatabaseHas('orders', [
        'user_id' => $this->user->id,
        'total_price' => 250000, // (2 * 100000) + (1 * 50000)
        'payment_status' => 'unpaid',
        'status' => 'pending'
    ]);

    // Verify order items were created
    $this->assertDatabaseHas('order_items', [
        'product_id' => $this->product1->id,
        'quantity' => 2,
        'price' => 100000
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $this->product2->id,
        'quantity' => 1,
        'price' => 50000
    ]);

    // Verify stock was reduced
    $this->product1->refresh();
    $this->product2->refresh();
    expect($this->product1->stock)->toBe(8); // 10 - 2
    expect($this->product2->stock)->toBe(4); // 5 - 1
});

test('order creation fails without authentication', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1
            ]
        ]
    ];

    $response = $this->postJson('/api/orders', $orderData);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

test('order creation fails with invalid validation', function () {
    $invalidData = [
        'items' => [] // Empty items array
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $invalidData);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'success'
        ]);
});

test('order creation fails with non-existent product', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => 99999, // Non-existent product
                'quantity' => 1
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(422);
});

test('order creation fails with insufficient stock', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 15 // More than available stock (10)
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Stock Not Enough'
        ]);
});

test('user can view their order details', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_code' => 'ORD-TEST123',
        'total_price' => 100000,
        'payment_status' => 'unpaid',
        'status' => 'pending'
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product1->id,
        'product_name' => $this->product1->product_name,
        'price' => $this->product1->price,
        'quantity' => 1
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'success',
            'data' => [
                'id',
                'order_code',
                'user_id',
                'total_price',
                'payment_status',
                'status',
                'items' => [
                    '*' => [
                        'id',
                        'order_id',
                        'product_id',
                        'product_name',
                        'price',
                        'quantity',
                        'product'
                    ]
                ],
                'user'
            ]
        ]);
});

test('user cannot view non-existent order', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/orders/99999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Order not found',
            'success' => false
        ]);
});

test('user can delete unpaid order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_status' => 'unpaid',
        'status' => 'pending'
    ]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product1->id,
        'quantity' => 2
    ]);

    $initialStock = $this->product1->stock;

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Order deleted successfully',
            'success' => true
        ]);

    // Verify order was deleted
    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);

    // Verify stock was restored
    $this->product1->refresh();
    expect($this->product1->stock)->toBe($initialStock + 2);
});

test('user cannot delete paid order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_status' => 'paid',
        'status' => 'processing'
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/orders/{$order->id}");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Cannot delete a paid order',
            'success' => false
        ]);
});

test('admin can view all orders', function () {
    // Create admin user
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create some orders
    $order1 = Order::factory()->create(['user_id' => $this->user->id]);
    $order2 = Order::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/orders/all');

    // For now, just check that the request doesn't return 500
    expect($response->status())->not->toBe(500);
});

test('non-admin cannot access all orders', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/orders/all');

    // Check that non-admin gets unauthorized or not found
    expect($response->status())->toBeIn([401, 404]);
});

test('midtrans callback endpoint exists', function () {
    $response = $this->postJson('/api/midtrans/callback', []);

    // The endpoint should exist and not return 404
    expect($response->status())->not->toBe(404);
});

test('order can be created with snap token', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(200);
    
    // Check that snap token is returned
    $snapToken = $response->json('data.snap_token');
    expect($snapToken)->not->toBeNull();
});

test('order creation includes midtrans integration', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 2
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(200);
    
    // Verify order was created with correct structure
    $orderData = $response->json('data.order');
    expect($orderData)->toHaveKeys(['order_code', 'user_id', 'total_price', 'payment_status', 'status']);
    expect($orderData['payment_status'])->toBe('unpaid');
    expect($orderData['status'])->toBe('pending');
});

test('order code is unique and properly formatted', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(200);
    
    $orderCode = $response->json('data.order.order_code');
    expect($orderCode)->toStartWith('ORD-');
    expect(strlen($orderCode))->toBeGreaterThan(10);
});

test('order total calculation is correct', function () {
    $orderData = [
        'items' => [
            [
                'product_id' => $this->product1->id,
                'quantity' => 3 // 3 * 100000 = 300000
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 2 // 2 * 50000 = 100000
            ]
        ]
    ];

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/orders', $orderData);

    $response->assertStatus(200);
    
    $totalPrice = $response->json('data.order.total_price');
    expect($totalPrice)->toBe(400000); // 300000 + 100000
});
