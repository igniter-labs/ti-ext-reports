<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\ReportRules;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\HourlySalesReportRule;

it('returns correct rule details', function(): void {
    $details = resolve(HourlySalesReportRule::class)->ruleDetails();

    expect($details)->toHaveKey('name')
        ->and($details)->toHaveKey('description')
        ->and($details['name'])->toBe('Hourly Sales Report')
        ->and($details['description'])->toBe('Shows sales performance broken down by each hour of the day.');
});

it('defines filters correctly', function(): void {
    $filters = resolve(HourlySalesReportRule::class)->defineFilters();

    expect($filters)->toBeArray()
        ->and($filters)->toHaveCount(4)

        ->and($filters[0]['id'])->toBe('hours')
        ->and($filters[0]['type'])->toBe('string')
        ->and($filters[0]['input'])->toBe('select')
        ->and($filters[0]['multiple'])->toBeTrue()
        ->and($filters[0]['values'])->toHaveCount(24)
        ->and($filters[0]['operators'])->toHaveCount(12)

        ->and($filters[1]['id'])->toBe('sales')
        ->and($filters[1]['type'])->toBe('double')
        ->and($filters[1]['input'])->toBe('number')
        ->and($filters[1]['operators'])->toHaveCount(10)

        ->and($filters[2]['id'])->toBe('covers')
        ->and($filters[2]['type'])->toBe('integer')
        ->and($filters[2]['input'])->toBe('number')
        ->and($filters[2]['operators'])->toHaveCount(10)

        ->and($filters[3]['id'])->toBe('orders')
        ->and($filters[3]['type'])->toBe('integer')
        ->and($filters[3]['input'])->toBe('number')
        ->and($filters[3]['operators'])->toHaveCount(10);
});

it('defines columns correctly', function(): void {
    $columns = resolve(HourlySalesReportRule::class)->defineColumns();

    expect($columns)->toHaveKeys([
        'hours',
        'sales',
        'covers',
        'orders',
    ]);
});

it('gets report data with date range correctly', function(): void {
    $menu = Menu::factory()->create(['menu_name' => 'Test Menu', 'menu_price' => 10.50]);
    Order::factory()->afterCreating(function(Order $order) use ($menu): void {
        $order->addOrderMenus([
            (object)[
                'id' => $menu->getKey(),
                'name' => $menu->menu_name,
                'qty' => 2,
                'price' => $menu->menu_price,
                'subtotal' => $menu->menu_price * 2,
                'comment' => '',
                'options' => [],
            ],
        ]);
    })->create([
        'order_date' => now()->subDay(),
        'order_time' => '15:30:00',
        'order_total' => 11.00,
    ]);

    $reportBuilder = new ReportBuilder;
    $reportBuilder->rule_class = HourlySalesReportRule::class;

    $result = $reportBuilder->getTableData(now()->subDays(30)->toDateString(), now()->addDays(3)->toDateString());

    expect($result->count())->toBe(1)
        ->and($result->all())->toBe([
            [
                'hours' => '3:00 PM',
                'sales' => currency_format(11),
                'covers' => 1,
                'orders' => 1,
            ],
        ]);
});
