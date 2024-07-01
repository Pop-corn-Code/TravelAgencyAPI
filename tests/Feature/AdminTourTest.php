<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;
    /**
    * A basic feature test example.
    */

    public function test_public_user_cannot_adding_tour(): void {
        $travel = Travel::factory()->create();
        $response = $this->postJson( '/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus( 401 );
    }

    public function test_non_admin_user_cannot_access_adding_tour(): void {
        $travel = Travel::factory()->create();
        $this->seed( RoleSeeder::class );
        $user = User::factory()->create();
        $user->roles()->attach( Role::where( 'name', 'editor' )->value( 'id' ) );

        $response = $this->actingAs( $user )->postJson( '/api/v1/admin/travels/'.$travel->id.'/tours' );

        $response->assertStatus( 403 );
    }

    public function test_saves_tour_successfully_with_valid_data(): void {
        // Seed roles into the database
        $this->seed( RoleSeeder::class );

        // Create a user and attach the 'admin' role to this user
        $user = User::factory()->create();
        $user->roles()->attach( Role::where( 'name', 'admin' )->value( 'id' ) );

        // Verify the user has the 'admin' role
        $this->assertTrue( $user->roles->contains( 'name', 'admin' ) );
        $travel = Travel::factory()->create();

        // Attempt to create a travel record with insufficient data
        $response = $this->actingAs($user)->postJson( '/api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'tour 123',
        ] );

        // Assert that the response status is 422 ( Unprocessable Entity ) due to validation failure
        $response->assertStatus( 422 );

        // Attempt to create a travel record with valid data
        $response = $this->actingAs( $user, 'sanctum' )->postJson( '/api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'Tour to Cameroon',
            'starting_date' => '2024-01-05',
            'ending_date' => '2024-01-20',
            'price' => 150,
        ] );

        // Assert that the response status is 201 ( Created )
        $response->assertStatus( 201 );

        // Fetch all travels and check if the new travel record is present
        $response = $this->get( '/api/v1/travels/'.$travel->slug.'/tours' );
        $response->assertJsonFragment( [ 'name' => 'Tour to Cameroon' ] );
    }

}
