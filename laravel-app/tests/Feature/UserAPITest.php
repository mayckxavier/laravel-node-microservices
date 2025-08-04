<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAPITest extends TestCase
{
    use RefreshDatabase;

    protected $requestUserData = [
        'name' => 'Mayck',
        'email' => 'mayck@email.com',
        'password' => 'mayckpassword',
        'password_confirmation' => 'mayckpassword'
    ];

    public function test_register_user_correctly(): void
    {
        $body = $this->requestUserData;

        $response = $this->post('api/users', $body);

        $response->assertStatus(201);

        $response->assertExactJson([
                'data' => [
                    'id' => 1,
                    'name' => 'Mayck',
                    'email' => 'mayck@email.com'
                ]
            ]
        );

        $this->assertDatabaseHas('users', [
            'name' => $body['name'],
            'email' => $body['email']
        ]);
    }

    public function test_register_user_validation_error_missing_name()
    {

        $body = $this->requestUserData;
        unset($body['name']);


        $response = $this->post('api/users', $body);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['name']);
    }

    public function test_register_user_validation_error_missing_email()
    {
        $body = $this->requestUserData;
        unset($body['email']);

        $response = $this->post('api/users', $body);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_register_user_validation_error_missing_password()
    {
        $body = $this->requestUserData;
        unset($body['password']);

        $response = $this->post('api/users', $body);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_register_user_validation_error_missing_password_confirmation()
    {
        $body = $this->requestUserData;
        unset($body['password_confirmation']);

        $response = $this->post('api/users', $body);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_get_all_ok()
    {
        User::factory()->count(3)->create();

        $this->assertDatabaseCount('users', 3);

        $response = $this->get('api/users');
        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(3, $body['data']);

        $response->assertJsonStructure([
            'data' => [
                ['id', 'name', 'email'],
                ['id', 'name', 'email'],
                ['id', 'name', 'email'],
            ]
        ]);
    }

    public function test_get_by_id(){
        $user = User::factory()->create();

        $response = $this->get(route('users.getById', $user->id));
        $response->assertStatus(200);
    }

    public function test_get_by_id_not_found(){
        $response = $this->get(route('users.getById', 1));
        $response->assertStatus(404);
    }
}
