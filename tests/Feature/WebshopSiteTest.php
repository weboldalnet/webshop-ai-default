<?php

namespace Weboldalnet\WebshopAiDefault\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Illuminate\Support\Facades\Session;

class WebshopSiteTest extends TestCase
{
    // Megjegyzés: RefreshDatabase csak akkor működik, ha a teszt adatbázis megfelelően be van állítva.
    // Mivel a projekt konvencióit nem ismerem pontosan a teszt DB-re nézve, alapvető működést tesztelek.

    protected function setUp(): void
    {
        parent::setUp();
        // Domain beállítások a tesztekhez
        config(['app.url' => 'http://localhost']);
    }

    /** @test */
    public function it_can_access_categories_index()
    {
        $response = $this->get('/webshop');
        $response->assertStatus(200);
        $response->assertViewIs('site.webshop.categories.index');
    }

    /** @test */
    public function it_can_add_product_to_cart()
    {
        $product = WebshopProduct::active()->first();
        if (!$product) {
            $this->markTestSkipped('Nincs aktív termék a teszteléshez.');
        }

        $response = $this->postJson('/webshop/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(1, count(Session::get('webshop_cart')));
    }

    /** @test */
    public function it_cannot_add_different_category_products_to_compare()
    {
        $product1 = WebshopProduct::active()->first();
        // Keressünk egy másik kategóriájú terméket
        $product2 = WebshopProduct::active()->where('category_id', '!=', $product1->category_id)->first();

        if (!$product1 || !$product2) {
            $this->markTestSkipped('Nincs elég különböző kategóriájú termék a teszteléshez.');
        }

        // Első hozzáadása
        $this->postJson('/webshop/compare/add', ['product_id' => $product1->id]);
        
        // Második (különböző kategória) hozzáadása
        $response = $this->postJson('/webshop/compare/add', ['product_id' => $product2->id]);

        $response->assertStatus(200)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_submit_checkout()
    {
        $product = WebshopProduct::active()->first();
        if (!$product) {
            $this->markTestSkipped('Nincs aktív termék a teszteléshez.');
        }

        // Kosárba rakás
        $this->postJson('/webshop/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post('/webshop/checkout', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '06301234567',
            'note' => 'Test order',
            'billing' => [
                'zip' => '1234',
                'city' => 'Budapest',
                'address' => 'Fő utca 1.'
            ],
            'shipping' => [
                'zip' => '1234',
                'city' => 'Budapest',
                'address' => 'Fő utca 1.'
            ]
        ]);

        $response->assertRedirect();
        $this->assertEmpty(Session::get('webshop_cart'));
    }
}
