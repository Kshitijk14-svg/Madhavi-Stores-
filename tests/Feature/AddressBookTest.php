<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressBookTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'label'       => 'Home',
        'first_name'  => 'Jane',
        'last_name'   => 'Doe',
        'phone'       => '9876543210',
        'address'     => '1 Test St',
        'city'        => 'Mumbai',
        'postal_code' => '400001',
    ];

    public function test_user_can_create_an_address_and_first_one_is_auto_defaulted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account.addresses.store'), $this->payload)
            ->assertRedirect();

        $address = Address::where('user_id', $user->id)->first();
        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
    }

    public function test_creating_a_second_default_unsets_the_first(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('account.addresses.store'), $this->payload);
        $this->post(route('account.addresses.store'), array_merge($this->payload, [
            'label' => 'Work', 'is_default' => '1',
        ]));

        $addresses = Address::where('user_id', $user->id)->get();
        $this->assertEquals(2, $addresses->count());
        $this->assertEquals(1, $addresses->where('is_default', true)->count());
        $this->assertEquals('Work', $addresses->firstWhere('is_default', true)->label);
    }

    public function test_user_can_update_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create(array_merge($this->payload, ['user_id' => $user->id, 'is_default' => true]));

        $this->actingAs($user)
            ->post(route('account.addresses.update', $address->id), array_merge($this->payload, ['city' => 'Pune']))
            ->assertRedirect();

        $this->assertEquals('Pune', $address->fresh()->city);
    }

    public function test_user_cannot_update_another_users_address(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceAddress = Address::create(array_merge($this->payload, ['user_id' => $alice->id, 'is_default' => true]));

        $this->actingAs($bob)
            ->post(route('account.addresses.update', $aliceAddress->id), array_merge($this->payload, ['city' => 'Hacked']))
            ->assertNotFound();

        $this->assertEquals('Mumbai', $aliceAddress->fresh()->city);
    }

    public function test_user_cannot_delete_another_users_address(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceAddress = Address::create(array_merge($this->payload, ['user_id' => $alice->id, 'is_default' => true]));

        $this->actingAs($bob)
            ->post(route('account.addresses.delete', $aliceAddress->id))
            ->assertNotFound();

        $this->assertNotNull($aliceAddress->fresh());
    }

    public function test_user_cannot_set_another_users_address_as_default(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $aliceAddress = Address::create(array_merge($this->payload, ['user_id' => $alice->id, 'is_default' => false]));

        $this->actingAs($bob)
            ->post(route('account.addresses.default', $aliceAddress->id))
            ->assertNotFound();

        $this->assertFalse($aliceAddress->fresh()->is_default);
    }

    public function test_deleting_the_default_address_promotes_another(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('account.addresses.store'), $this->payload);
        $first = Address::where('user_id', $user->id)->first();

        $this->post(route('account.addresses.store'), array_merge($this->payload, ['label' => 'Work']));

        $this->post(route('account.addresses.delete', $first->id));

        $remaining = Address::where('user_id', $user->id)->get();
        $this->assertEquals(1, $remaining->count());
        $this->assertTrue($remaining->first()->is_default);
    }

    public function test_checkout_page_renders_saved_addresses_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        Address::create(array_merge($this->payload, ['user_id' => $user->id, 'is_default' => true]));

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertOk();
        $response->assertSee('Mumbai');
        $response->assertSee('400001');
    }
}
