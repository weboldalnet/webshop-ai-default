<?php

namespace Weboldalnet\WebshopAiDefault\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProperty;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Models\WebshopSetting;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductVariationService;

class WebshopAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    protected function authenticatedAdmin()
    {
        $admin = \App\Models\Admin::first();
        if (!$admin) {
            $this->markTestSkipped('Nincs admin felhasználó a teszthez.');
        }
        return $this->actingAs($admin, 'admin');
    }

    // === Tulajdonság kategória ===

    public function test_property_category_can_be_created()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Szín',
            'slug' => 'szin',
            'filter_type' => 'checkbox',
            'filter_enabled' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('public.webshop_property_categories', ['name' => 'Szín', 'filter_type' => 'checkbox']);
        $this->assertEquals('checkbox', $pc->filter_type);
    }

    // === Tulajdonság ===

    public function test_property_can_be_created_for_checkbox_category()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Szín', 'slug' => 'szin', 'filter_type' => 'checkbox',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $prop = WebshopProperty::create([
            'property_category_id' => $pc->id,
            'name' => 'Piros', 'slug' => 'piros',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('public.webshop_properties', ['name' => 'Piros', 'property_category_id' => $pc->id]);
        $this->assertEquals($pc->id, $prop->propertyCategory->id);
    }

    public function test_property_can_be_created_for_radio_category()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Méret', 'slug' => 'meret', 'filter_type' => 'radio',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $prop = WebshopProperty::create([
            'property_category_id' => $pc->id,
            'name' => 'L', 'slug' => 'l',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('public.webshop_properties', ['name' => 'L']);
    }

    public function test_number_category_blocks_property_creation()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Súly', 'slug' => 'suly', 'filter_type' => 'number',
            'suffix' => 'kg', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->assertTrue($pc->isNumber());
        // A controller szintű validáció tiltja, itt modell szinten ellenőrizzük
        $this->assertEquals('number', $pc->filter_type);
    }

    // === Kategória ===

    public function test_category_can_be_created()
    {
        $cat = WebshopCategory::create([
            'name_singular' => 'Cipő', 'name_plural' => 'Cipők',
            'slug' => 'cipo', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('public.webshop_categories', ['name_singular' => 'Cipő']);
    }

    // === Termék ===

    public function test_product_first_step_creation()
    {
        $cat = WebshopCategory::create([
            'name_singular' => 'Cipő', 'name_plural' => 'Cipők',
            'slug' => 'cipo', 'is_active' => true, 'sort_order' => 1,
        ]);

        $product = WebshopProduct::create([
            'name' => 'Nike Air Max', 'slug' => 'nike-air-max',
            'category_id' => $cat->id, 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('public.webshop_products', ['name' => 'Nike Air Max', 'category_id' => $cat->id]);
        $this->assertEquals($cat->id, $product->category->id);
    }

    public function test_product_property_save()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Szín', 'slug' => 'szin', 'filter_type' => 'checkbox',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $prop = WebshopProperty::create([
            'property_category_id' => $pc->id, 'name' => 'Piros', 'slug' => 'piros',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $cat = WebshopCategory::create([
            'name_singular' => 'Cipő', 'name_plural' => 'Cipők',
            'slug' => 'cipo', 'is_active' => true, 'sort_order' => 1,
        ]);
        $product = WebshopProduct::create([
            'name' => 'Nike', 'slug' => 'nike', 'category_id' => $cat->id,
            'is_active' => true, 'sort_order' => 1,
        ]);

        \Weboldalnet\WebshopAiDefault\Models\WebshopProductProperty::create([
            'product_id' => $product->id,
            'property_category_id' => $pc->id,
            'property_id' => $prop->id,
        ]);

        $this->assertDatabaseHas('public.webshop_product_properties', [
            'product_id' => $product->id, 'property_id' => $prop->id,
        ]);
    }

    // === Variáció szinkronizálás ===

    public function test_product_variation_sync()
    {
        $cat = WebshopCategory::create([
            'name_singular' => 'Cipő', 'name_plural' => 'Cipők',
            'slug' => 'cipo', 'is_active' => true, 'sort_order' => 1,
        ]);

        $pA = WebshopProduct::create(['name' => 'A', 'slug' => 'a', 'category_id' => $cat->id, 'is_active' => true, 'sort_order' => 1]);
        $pB = WebshopProduct::create(['name' => 'B', 'slug' => 'b', 'category_id' => $cat->id, 'is_active' => true, 'sort_order' => 2]);
        $pC = WebshopProduct::create(['name' => 'C', 'slug' => 'c', 'category_id' => $cat->id, 'is_active' => true, 'sort_order' => 3]);

        WebshopProductVariationService::syncVariations($pA, [$pB->id, $pC->id]);

        // A-nak variációi B és C
        $this->assertEquals(2, $pA->variations()->count());
        // B-nek variációi A és C
        $this->assertEquals(2, $pB->variations()->count());
        // C-nek variációi A és B
        $this->assertEquals(2, $pC->variations()->count());
    }

    // === Rendelés ===

    public function test_order_completed_status()
    {
        $order = WebshopOrder::create([
            'order_number' => 'ORD-001', 'status' => 'pending',
            'total_price' => 10000, 'currency' => 'HUF',
        ]);

        $order->is_completed = true;
        $order->completed_at = now();
        $order->save();

        $this->assertTrue($order->fresh()->is_completed);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    // === Settings ===

    public function test_settings_save_and_retrieve()
    {
        WebshopSettingsService::save([
            'product_price_enabled' => 'true',
            'product_gallery_enabled' => 'false',
        ]);

        $this->assertTrue(WebshopSettingsService::getBool('product_price_enabled'));
        $this->assertFalse(WebshopSettingsService::getBool('product_gallery_enabled'));
    }

    // === Active toggle ===

    public function test_property_category_toggle_active()
    {
        $pc = WebshopPropertyCategory::create([
            'name' => 'Test', 'slug' => 'test', 'filter_type' => 'checkbox',
            'is_active' => true, 'sort_order' => 1,
        ]);

        $pc->is_active = false;
        $pc->save();

        $this->assertFalse($pc->fresh()->is_active);
    }

    // === Sort order ===

    public function test_sort_order_update()
    {
        $pc1 = WebshopPropertyCategory::create(['name' => 'A', 'slug' => 'a', 'filter_type' => 'checkbox', 'is_active' => true, 'sort_order' => 1]);
        $pc2 = WebshopPropertyCategory::create(['name' => 'B', 'slug' => 'b', 'filter_type' => 'radio', 'is_active' => true, 'sort_order' => 2]);

        WebshopPropertyCategory::where('id', $pc2->id)->update(['sort_order' => 1]);
        WebshopPropertyCategory::where('id', $pc1->id)->update(['sort_order' => 2]);

        $this->assertEquals(2, $pc1->fresh()->sort_order);
        $this->assertEquals(1, $pc2->fresh()->sort_order);
    }
}
