<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase {
    use RefreshDatabase;
    /**
    * A basic feature test example.
    */

    public function test_public_user_cannot_adding_travel(): void {
        $response = $this->postJson( '/api/v1/admin/travels' );

        $response->assertStatus( 401 );
    }

    public function test_non_admin_user_cannot_access_adding_travel(): void {
        $this->seed( RoleSeeder::class );
        $user = User::factory()->create();
        $user->roles()->attach( Role::where( 'name', 'editor' )->value( 'id' ) );

        $response = $this->actingAs( $user )->postJson( '/api/v1/admin/travels' );

        $response->assertStatus( 403 );
    }

    public function test_saves_travel_successfully_with_valid_data(): void {
        // Seed roles into the database
        $this->seed( RoleSeeder::class );

        // Create a user and attach the 'admin' role to this user
        $user = User::factory()->create();
        $user->roles()->attach( Role::where( 'name', 'admin' )->value( 'id' ) );

        // Verify the user has the 'admin' role
        $this->assertTrue( $user->roles->contains( 'name', 'admin' ) );

        // Attempt to create a travel record with insufficient data
        $response = $this->actingAs($user)->postJson( '/api/v1/admin/travels', [
            'name' => 'Travel 123',
        ] );

        // Assert that the response status is 422 ( Unprocessable Entity ) due to validation failure
        $response->assertStatus( 422 );

        // Attempt to create a travel record with valid data
        $response = $this->actingAs( $user, 'sanctum' )->postJson( '/api/v1/admin/travels', [
            'name' => 'Travel to Cameroon',
            'description' => 'Travel to Cameroon description test',
            'is_public' => true,
            'number_of_days' => 9,
        ] );

        // Assert that the response status is 201 ( Created )
        $response->assertStatus( 201 );

        // Fetch all travels and check if the new travel record is present
        $response = $this->get( '/api/v1/travels' );
        $response->assertJsonFragment( [ 'name' => 'Travel to Cameroon' ] );
    }

}
