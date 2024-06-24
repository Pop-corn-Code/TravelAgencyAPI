<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TourListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_tours_list_sorts_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'starting_date'=>now()->addDays(2),
            'eding_date'=>now()->addDays(3),
        ]);

        $earlyTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'starting_date'=>now(),
            'eding_date'=>now()->addDays(1),
        ]);

        $response = $this->get('api/v1/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $earlyTour->id);
        $response->assertJsonPath('data.1.id', $laterTour->id);

        $response->assertStatus(200);
    }

    public function test_tours_list_sorts_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();

        $expensiveTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'price'=> 200,
        ]);
        
        $cheapLaterTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'price'=> 100,
            'starting_date'=>now()->addDays(2),
            'ending_date'=>now()->addDays(3),
        ]);
        
        $cheapEarlierTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'price'=> 100,
            'starting_date'=>now(),
            'ending_date'=>now()->addDays(2),
        ]);

        $response=$this->get('api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc');

        $response->assertStatus(200);
        
        $response->assertJsonPath('data.2.id', $expensiveTour->id);
        $response->assertJsonPath('data.0.id', $cheapEarlierTour->id);
        $response->assertJsonPath('data.1.id', $cheapLaterTour->id);
        
    }

    public function test_tours_list_filters_by_price_correctly(): void{
        $travel = Travel::factory()->create();

        $tour1 = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'price'=> 200,
        ]);
        
        $tour2 = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'price'=> 100,
            'starting_date'=>now()->addDays(2),
            'ending_date'=>now()->addDays(3),
        ]);

        // $endpoint = '/api/v1/'. $travel->slug .'/tours?priceFrom=10&priceTo=150';
        $endpoint = '/api/v1/'. $travel->slug .'/tours';

        // $response->assertStatus(200);
        // $response->assertJsonPath('data.0.id', $tour2->id);
           
        $response = $this->get($endpoint.'?priceFrom=100');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$tour1->id]);
        $response->assertJsonFragment(['id'=>$tour2->id]);

        $response = $this->get($endpoint.'?priceFrom=250');
        $response->assertJsonCount(0, 'data');
        
        $response = $this->get($endpoint.'?priceTo=200');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$tour1->id]);
        $response->assertJsonFragment(['id'=>$tour2->id]);
        
        $response = $this->get($endpoint.'?priceTo=150');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$tour1->id]);
        $response->assertJsonFragment(['id'=>$tour2->id]);
        
        $response = $this->get($endpoint.'?priceFrom=150&priceTo=250');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id'=>$tour1->id]);
        $response->assertJsonMissing(['id'=>$tour2->id]);
    }

    
    public function test_tours_list_filters_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'starting_date'=>now()->addDays(2),
            'eding_date'=>now()->addDays(3),
        ]);

        $earlyTour = Tour::factory()->create([
            'travel_id'=>$travel->id,
            'starting_date'=>now(),
            'eding_date'=>now()->addDays(1),
        ]);

        $endpoint = '/api/v1/'. $travel->slug .'/tours';

        $response = $this->get($endpoint.'?dateFrom='.now());
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id'=>$laterTour->id]);
        $response->assertJsonFragment(['id'=>$earlyTour->id]);

        $response = $this->get($endpoint.'?dateFrom='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id'=>$laterTour->id]);
        $response->assertJsonMissing(['id'=>$earlyTour->id]);

        $response = $this->get($endpoint.'?dateTo='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$laterTour->id]);
        $response->assertJsonFragment(['id'=>$earlyTour->id]);
        
        $response = $this->get($endpoint.'?dateFrom='.now()->addDay().'&dateTo='.now()->addDays(5));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id'=>$earlyTour->id]);
        $response->assertJsonFragment(['id'=>$laterTour->id]);
    }

    public function test_tours_list_return_validation_errors():void
    {
        $travel = Travel::factory()->create();

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?dateFrom=abcd');
        $response->assertStatus(422);
        
        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?priceFrom=abcd');
        $response->assertStatus(422);
    }
}
