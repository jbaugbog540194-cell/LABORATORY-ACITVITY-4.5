<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_staff_can_view_orders_index(): void
    {
        $user = User::factory()->create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
        ]);

        $this->actingAs($user)
            ->get('/orders')
            ->assertOk()
            ->assertSee('Order Number')
            ->assertSee('Customer');
    }
}
