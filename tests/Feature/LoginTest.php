<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_login_return_valid_credentials(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/v1/login', [
            'email'=>$user->email,
            'password'=>'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
        // return $user->eamil;
    }
    public function test_login_returns_errors_with_invalid_credentials(): void{
        $response = $this->postJson('/api/v1/login',[
            'email'=>'testing@gmail.com',
            'password'=>'password',
        ]);

        $response->assertStatus(422);
    }
}
