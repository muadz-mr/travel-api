<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.00]);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '123.00']);
    }

    public function test_tours_list_returns_pagination(): void
    {
        $toursPerPage = config('app.pagination_per_page.tours');

        $travel = Travel::factory()->create();
        Tour::factory($toursPerPage + 1)->create(['travel_id' => $travel->id]);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours");
        $response->assertStatus(200);
        $response->assertJsonCount($toursPerPage, 'data');
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_tours_list_sorts_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2)->toDateString(),
            'ending_date' => now()->addDays(3)->toDateString(),
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDays(1)->toDateString(),
        ]);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours");
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $earlierTour->id);
        $response->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_sorts_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2)->toDateString(),
            'ending_date' => now()->addDays(3)->toDateString(),
        ]);
        $cheapEarlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDays(1)->toDateString(),
        ]);

        $response = $this->get("/api/v1/travels/{$travel->slug}/tours?sortBy=price&sortOrder=asc");
        $response->assertStatus(200);
        // sort by price,asc then by starting_date,asc
        $response->assertJsonPath('data.0.id', $cheapEarlierTour->id);
        $response->assertJsonPath('data.1.id', $cheapLaterTour->id);
        $response->assertJsonPath('data.2.id', $expensiveTour->id);
    }

    public function test_tours_list_filters_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
        ]);

        $endpoint = "/api/v1/travels/{$travel->slug}/tours";

        $response = $this->get("$endpoint?priceFrom=100");
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $expensiveTour->id]);
        $response->assertJsonFragment(['id' => $cheapTour->id]);

        $response = $this->get("$endpoint?priceFrom=150");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expensiveTour->id]);

        $response = $this->get("$endpoint?priceFrom=250");
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        $response = $this->get("$endpoint?priceTo=200");
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $expensiveTour->id]);
        $response->assertJsonFragment(['id' => $cheapTour->id]);

        $response = $this->get("$endpoint?priceTo=150");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $expensiveTour->id]);
        $response->assertJsonFragment(['id' => $cheapTour->id]);

        $response = $this->get("$endpoint?priceTo=50");
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        $response = $this->get("$endpoint?priceFrom=150&priceTo=250");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expensiveTour->id]);
    }

    public function test_tours_list_filters_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2)->toDateString(),
            'ending_date' => now()->addDays(3)->toDateString(),
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDays(1)->toDateString(),
        ]);

        $endpoint = "/api/v1/travels/{$travel->slug}/tours";

        $response = $this->get("$endpoint?dateFrom=" . now()->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $laterTour->id]);
        $response->assertJsonFragment(['id' => $earlierTour->id]);

        $response = $this->get("$endpoint?dateFrom=" . now()->addDay()->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $earlierTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        $response = $this->get("$endpoint?dateFrom=" . now()->addDays(5)->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        $response = $this->get("$endpoint?dateTo=" . now()->addDays(5)->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $laterTour->id]);
        $response->assertJsonFragment(['id' => $earlierTour->id]);

        $response = $this->get("$endpoint?dateTo=" . now()->addDay()->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $laterTour->id]);
        $response->assertJsonFragment(['id' => $earlierTour->id]);

        $response = $this->get("$endpoint?dateTo=" . now()->subDay()->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');

        $response = $this->get("$endpoint?dateFrom=" . now()->addDay()->toDateString() . "&dateTo=" . now()->addDays(5)->toDateString());
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $earlierTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);
    }

    public function test_tours_list_returns_validation_errors(): void
    {
        $travel = Travel::factory()->create();

        $endpoint = "/api/v1/travels/{$travel->slug}/tours";

        $response = $this->getJson($endpoint.'?dateFrom=test');
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('dateFrom');

        $response = $this->getJson($endpoint.'?priceFrom=test');
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('priceFrom');
    }
}
