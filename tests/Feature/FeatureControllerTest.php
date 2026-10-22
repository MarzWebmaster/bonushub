<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * These routes sit behind the auth middleware. The previous version of this file requested them as a guest and
 * expected 200 plus view names (finance.index, shop.index ...) that no longer exist, so it could only fail.
 */
class FeatureControllerTest extends TestCase
{
    #[DataProvider('protectedPages')]
    public function test_guests_are_sent_to_the_login_page(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    public static function protectedPages(): array
    {
        return [
            'finance'         => ['/finance'],
            'manage shop'     => ['/manage/shop'],
            'settings'        => ['/settings'],
            'staff dashboard' => ['/staff/dashboard'],
            'merchant'        => ['/merchant/dashboard'],
            'redemptions'     => ['/merchant/redemptions'],
            'superadmin'      => ['/superadmin/dashboard'],
        ];
    }
}
