<?php

namespace Tests\Feature;

use Tests\TestCase;

class FeatureControllerTest extends TestCase
{
    /**
     * Test the finance index route.
     */
    public function test_finance_index_route()
    {
        $response = $this->get('/finance');
        $response->assertStatus(200);
        $response->assertViewIs('finance.index');
    }

    /**
     * Test the shop index route.
     */
    public function test_shop_index_route()
    {
        $response = $this->get('/manage/shop');
        $response->assertStatus(200);
        $response->assertViewIs('shop.index');
    }

    /**
     * Test the package index route.
     */
    public function test_package_index_route()
    {
        $response = $this->get('/manage/shop/package');
        $response->assertStatus(200);
        $response->assertViewIs('package.index');
    }

    /**
     * Test the settings index route.
     */
    public function test_settings_index_route()
    {
        $response = $this->get('/settings');
        $response->assertStatus(200);
        $response->assertViewIs('settings.index');
    }
}
