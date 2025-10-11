<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('admin dapat membuat product baru', function () {

    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);


    $productData = [
        'product_name' => 'Test Product',
        'description' => 'Deskripsi produk untuk testing',
        'price' => 100000,
        'stock' => 50,
        'photo_product' => UploadedFile::fake()->image('product.jpg', 800, 600)
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/products', $productData);

    $response->assertStatus(201)
        ->assertJson([
            'messages' => 'Product Created',
            'success' => true,
        ]);


    $this->assertDatabaseHas('products', [
        'product_name' => 'Test Product',
        'description' => 'Deskripsi produk untuk testing',
        'price' => 100000,
        'stock' => 50,
    ]);
});

test('admin dapat mengupdate product', function () {

    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);


    $product = Product::factory()->create([
        'product_name' => 'Product Lama',
        'description' => 'Deskripsi lama',
        'price' => 50000,
        'stock' => 25,
    ]);


    $updateData = [
        'product_name' => 'Product Baru',
        'description' => 'Deskripsi baru yang lebih baik',
        'price' => 150000,
        'stock' => 75,
        'photo_product' => UploadedFile::fake()->image('new-product.jpg', 800, 600)
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/products/{$product->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'messages' => 'Product Updated',
            'sucess' => true,
        ]);


    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'product_name' => 'Product Baru',
        'description' => 'Deskripsi baru yang lebih baik',
        'price' => 150000,
        'stock' => 75,
    ]);
});

test('admin dapat menghapus product', function () {

    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);


    $product = Product::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'messages' => 'Product Delted',
            'sucess' => true,
        ]);


    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('user tanpa role admin tidak dapat membuat product', function () {

    $user = User::factory()->create(['role' => 'user']);
    $token = JWTAuth::fromUser($user);

    $productData = [
        'product_name' => 'Test Product',
        'description' => 'Deskripsi produk',
        'price' => 100000,
        'stock' => 50,
        'photo_product' => UploadedFile::fake()->image('product.jpg')
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/products', $productData);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthorized',
            'success' => false,
        ]);
});

test('user tanpa role admin tidak dapat mengupdate product', function () {

    $user = User::factory()->create(['role' => 'user']);
    $token = JWTAuth::fromUser($user);


    $product = Product::factory()->create();

    $updateData = [
        'product_name' => 'Product Baru',
        'description' => 'Deskripsi baru',
        'price' => 150000,
        'stock' => 75,
        'photo_product' => UploadedFile::fake()->image('new-product.jpg')
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson("/api/products/{$product->id}", $updateData);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthorized',
            'success' => false,
        ]);
});

test('user tanpa role admin tidak dapat menghapus product', function () {

    $user = User::factory()->create(['role' => 'user']);
    $token = JWTAuth::fromUser($user);


    $product = Product::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthorized',
            'success' => false,
        ]);
});

test(' melihat semua product ', function () {

    Product::factory()->count(3)->create();

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJson([
            'messages' => 'Get All Data Products',
            'sucess' => true,
        ])
        ->assertJsonStructure([
            'messages',
            'data' => [
                '*' => [
                    'id',
                    'product_name',
                    'description',
                    'price',
                    'stock',
                    'photo_product',
                    'created_at',
                    'updated_at'
                ]
            ],
            'sucess'
        ]);


    $this->assertCount(3, $response->json('data'));
});

test('melihat product berdasarkan id ', function () {

    $product = Product::factory()->create([
        'product_name' => 'Product Test',
        'description' => 'Deskripsi test',
        'price' => 75000,
        'stock' => 30,
    ]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'messages' => 'Get Product By Id',
            'sucess' => true,
        ])
        ->assertJsonStructure([
            'messages',
            'sucess',
            'data' => [
                'id',
                'product_name',
                'description',
                'price',
                'stock',
                'photo_product',
                'created_at',
                'updated_at'
            ]
        ]);


    $this->assertEquals('Product Test', $response->json('data.product_name'));
    $this->assertEquals('Deskripsi test', $response->json('data.description'));
    $this->assertEquals(75000, $response->json('data.price'));
    $this->assertEquals(30, $response->json('data.stock'));
});
