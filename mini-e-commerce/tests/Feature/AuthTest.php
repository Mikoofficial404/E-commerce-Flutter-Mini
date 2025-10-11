<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

describe('AuthController', function () {

    describe('Registrasi Pengguna', function () {

        test('dapat mendaftarkan pengguna baru dengan data yang valid', function () {
            $userData = [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $response = $this->postJson('/api/register', $userData);

            $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Register User',
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'message',
                    'success',
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ]);

            $this->assertDatabaseHas('users', [
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);

            $user = User::where('email', 'test@example.com')->first();
            expect(Hash::check('password123', $user->password))->toBeTrue();
        });

        test('registrasi gagal jika email sudah digunakan', function () {
            User::factory()->create(['email' => 'test@example.com']);

            $userData = [
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $response = $this->postJson('/api/register', $userData);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ])
                ->assertJsonPath('message.email', ['The email has already been taken.']);
        });
    });

    describe('Login Pengguna', function () {

        beforeEach(function () {
            $this->user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);
        });

        test('dapat login dengan kredensial yang valid', function () {
            $loginData = [
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $response = $this->postJson('/api/login', $loginData);

            $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Login Success',
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'message',
                    'success',
                    'token',
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ]);

            $token = $response->json('token');
            expect($token)->not->toBeNull();

            $decodedToken = JWTAuth::setToken($token)->getPayload();
            expect((int) $decodedToken->get('sub'))->toBe($this->user->id);
        });

        test('login gagal dengan kredensial yang salah', function () {
            $loginData = [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ];

            $response = $this->postJson('/api/login', $loginData);

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Maaf email atau Password Salah',
                    'success' => false,
                ]);
        });
    });
});
