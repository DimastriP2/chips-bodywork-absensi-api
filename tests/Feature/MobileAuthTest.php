<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('mobile login issues a token without a web session and replaces old tokens', function () {
    $user = User::factory()->create();
    $old = $user->createToken('old')->accessToken;
    $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()->assertJsonStructure(['message', 'token', 'user'])
        ->assertJsonMissingPath('user.password');
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $old->id]);
    expect($user->tokens()->count())->toBe(1);
    $this->assertGuest('web');
    $this->app['auth']->forgetGuards();
    $this->withToken($response->json('token'))->getJson('/api/profile')
        ->assertOk()->assertJsonPath('user.id', $user->id);
});

test('login rejects bad credentials and limits repeated attempts', function () {
    $user = User::factory()->create();
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertUnauthorized();
    }
    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrect'])
        ->assertStatus(429);
    expect($user->tokens()->count())->toBe(0);
});

test('logout revokes only the bearer token that made the request', function () {
    $user = User::factory()->create();
    $current = $user->createToken('current');
    $other = $user->createToken('other');
    $this->withToken($current->plainTextToken)->postJson('/api/logout')->assertOk();
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $current->accessToken->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $other->accessToken->id]);
    $this->app['auth']->forgetGuards();
    $this->withToken($current->plainTextToken)->getJson('/api/profile')->assertUnauthorized();
});

test('password change checks the old password and revokes other mobile tokens', function () {
    $user = User::factory()->create();
    $current = $user->createToken('current');
    $other = $user->createToken('other');
    $this->withToken($current->plainTextToken)->postJson('/api/change-password', [
        'old_password' => 'incorrect',
        'new_password' => 'new-password-123',
        'new_password_confirmation' => 'new-password-123',
    ])->assertUnprocessable();
    $this->withToken($current->plainTextToken)->postJson('/api/change-password', [
        'old_password' => 'password',
        'new_password' => 'new-password-123',
        'new_password_confirmation' => 'new-password-123',
    ])->assertOk();
    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $current->accessToken->id]);
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->accessToken->id]);
});

test('structured password values are validation errors', function () {
    $this->postJson('/api/login', [
        'email' => 'demo@example.test', 'password' => ['invalid'],
    ])->assertUnprocessable()->assertJsonValidationErrors('password');
});
