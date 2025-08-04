<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user_correctly(): void
    {
        $userData = [
            'name' => 'Mayck',
            'email' => 'mayck@email.com',
            'password' => 'mayckpassword',
            'password_confirmation' => 'mayckpassword'
        ];


        $response = $this->post('api/user', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
    }
}
