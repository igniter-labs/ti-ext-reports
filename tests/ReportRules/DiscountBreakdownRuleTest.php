<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\ReportRules;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Coupons\Models\Coupon;
use Igniter\Coupons\Models\CouponHistory;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\DiscountBreakdownRule;

it('returns correct rule details', function(): void {
    $details = resolve(DiscountBreakdownRule::class)->ruleDetails();

    expect($details)->toHaveKey('name')
        ->and($details)->toHaveKey('description')
        ->and($details['name'])->toBe('Discount Breakdown')
        ->and($details['description'])->toBe('Shows a breakdown of discounts applied to orders.');
});

it('defines filters correctly', function(): void {
    $filters = resolve(DiscountBreakdownRule::class)->defineFilters();

    expect($filters)->toBeArray()
        ->and($filters)->toHaveCount(3)

        ->and($filters[0]['id'])->toBe('quantity')
        ->and($filters[0]['type'])->toBe('integer')
        ->and($filters[0]['input'])->toBe('number')
        ->and($filters[0]['operators'])->toHaveCount(10)

        ->and($filters[1]['id'])->toBe('menu_amount')
        ->and($filters[1]['type'])->toBe('double')
        ->and($filters[1]['input'])->toBe('number')
        ->and($filters[1]['operators'])->toHaveCount(10)

        ->and($filters[2]['id'])->toBe('total_discount_amount')
        ->and($filters[2]['type'])->toBe('double')
        ->and($filters[2]['input'])->toBe('number')
        ->and($filters[2]['operators'])->toHaveCount(10);
});

it('defines columns correctly', function(): void {
    $columns = resolve(DiscountBreakdownRule::class)->defineColumns();

    expect($columns)->toHaveKeys([
        'coupon_name',
        'menu_item',
        'menu_amount',
        'quantity',
        'total_discount_amount',
    ]);
});

it('gets report data with date range correctly', function(): void {
    $menu = Menu::factory()->create(['menu_name' => 'Test Menu', 'menu_price' => 10.50]);
    $coupon = Coupon::factory()->afterCreating(function(Coupon $coupon) use ($menu): void {
        $coupon->addMenus([$menu->getKey()]);
    })->create(['name' => 'Test Coupon']);
    Order::factory()->afterCreating(function(Order $order) use ($menu, $coupon): void {
        $order->addOrderMenus([
            (object)[
                'id' => $menu->getKey(),
                'name' => $menu->menu_name,
                'qty' => 2,
                'price' => $menu->menu_price,
                'subtotal' => ($menu->menu_price * 2) - 10,
                'comment' => '',
                'options' => [],
            ],
        ]);

        CouponHistory::factory()->create([
            'coupon_id' => $coupon->getKey(),
            'order_id' => $order->getKey(),
            'status' => 1,
        ]);
    })->create();

    $reportBuilder = new ReportBuilder;
    $reportBuilder->rule_class = DiscountBreakdownRule::class;

    $result = $reportBuilder->getTableData(now()->subDays(30)->toDateString(), now()->addDays(3)->toDateString());

    expect($result->count())->toBe(1)
        ->and($result->all())->toBe([
            [
                'coupon_name' => 'Test Coupon',
                'menu_item' => 'Test Menu',
                'menu_amount' => currency_format(10.50),
                'quantity' => 2,
                'total_discount_amount' => currency_format(10),
            ],
        ]);
});
