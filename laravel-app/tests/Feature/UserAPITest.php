<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_get_by_id()
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.getById', $user->id));
        $response->assertStatus(200);
    }

    public function test_get_by_id_not_found()
    {
        $response = $this->get(route('users.getById', 1));
        $response->assertStatus(404);
    }

    public function test_update_user_all_fields(): void
    {
        $user = User::factory()->create();

        $updateData = $this->requestUserData;

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $this->requestUserData['name'],
                'email' => $this->requestUserData['email']
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $this->requestUserData['name'],
            'email' => $this->requestUserData['email']
        ]);

        $this->assertTrue(Hash::check($this->requestUserData['password'], User::find($user->id)->password));
    }

    public function test_update_user_only_name(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;

        $updateData = [
            'name' => $this->requestUserData['name']
        ];

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $this->requestUserData['name'],
                'email' => $originalEmail
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $this->requestUserData['name'],
            'email' => $originalEmail
        ]);
    }

    public function test_update_user_not_found(): void
    {
        $nonExistentId = 9999;

        $updateData = [
            'name' => $this->requestUserData['name']
        ];

        $response = $this->put(route('users.update', $nonExistentId), $updateData);
        $response->assertStatus(404);
    }

    public function test_update_user_validation_error_invalid_email(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'email' => 'email-invalido'
        ];

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_update_user_validation_error_password_too_short(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'password' => '123',
            'password_confirmation' => '123'
        ];

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_update_user_validation_error_password_not_confirmed(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'password' => $this->requestUserData['password']
        ];

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_update_user_email_unique_validation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $updateData = [
            'email' => $user2->email
        ];

        $response = $this->put(route('users.update', $user1->id), $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_update_user_same_email_allowed(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => $this->requestUserData['name'],
            'email' => $user->email
        ];

        $response = $this->put(route('users.update', $user->id), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $this->requestUserData['name'],
                'email' => $user->email
            ]
        ]);
    }
}
