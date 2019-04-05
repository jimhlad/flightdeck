<?php

namespace Yab\FlightDeck\Tests;

use Illuminate\Http\Response;
use Yab\FlightDeck\Models\User;
use Yab\FlightDeck\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function user_email_and_password_are_required_to_authenticate()
    {
        $response = $this->post(route('login'));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonStructure([
            'errors' => [
                'email',
                'password',
            ],
        ]);
    }

    /** @test */
    public function user_cannot_authenticate_with_invalid_credentials()
    {
        $this->withoutExceptionHandling();
        
        $user = factory(User::class)->create(['password' => bcrypt('testing123')]);
        
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'testing456',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function user_can_authenticate_with_valid_credentials()
    {
        $user = factory(User::class)->create(['password' => bcrypt('testing123')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'testing123',
        ]);

        $this->assertTrue(auth()->check());

        $this->assertEquals($user->email, auth()->user()->email);
    }

    /** @test */
    public function an_authenticated_user_can_logout()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $this->assertFalse(auth()->check());
    }
}

