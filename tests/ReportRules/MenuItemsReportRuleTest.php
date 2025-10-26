<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\ReportRules;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\MenuItemsReportRule;

it('returns correct rule details', function(): void {
    $details = resolve(MenuItemsReportRule::class)->ruleDetails();

    expect($details)->toHaveKey('name')
        ->and($details)->toHaveKey('description')
        ->and($details['name'])->toBe('Menu Items Report')
        ->and($details['description'])->toBe('Displays a list of menu items sold.');
});

it('defines filters correctly', function(): void {
    $filters = resolve(MenuItemsReportRule::class)->defineFilters();

    expect($filters)->toBeArray()
        ->and($filters)->toHaveCount(4)

        ->and($filters[0]['id'])->toBe('completed_order_amount')
        ->and($filters[0]['type'])->toBe('double')
        ->and($filters[0]['input'])->toBe('number')
        ->and($filters[0]['operators'])->toHaveCount(10)

        ->and($filters[1]['id'])->toBe('completed_order_quantity')
        ->and($filters[1]['type'])->toBe('integer')
        ->and($filters[1]['input'])->toBe('number')
        ->and($filters[1]['operators'])->toHaveCount(10)

        ->and($filters[2]['id'])->toBe('canceled_order_amount')
        ->and($filters[2]['type'])->toBe('double')
        ->and($filters[2]['input'])->toBe('number')
        ->and($filters[2]['operators'])->toHaveCount(10)

        ->and($filters[3]['id'])->toBe('canceled_order_quantity')
        ->and($filters[3]['type'])->toBe('integer')
        ->and($filters[3]['input'])->toBe('number')
        ->and($filters[3]['operators'])->toHaveCount(10);
});

it('defines columns correctly', function(): void {
    $columns = resolve(MenuItemsReportRule::class)->defineColumns();

    expect($columns)->toHaveKeys([
        'menu_item',
        'completed_order_amount',
        'completed_order_quantity',
        'cancelled_order_amount',
        'cancelled_order_quantity',
    ]);
});

it('gets report data with date range correctly', function(): void {
    Menu::factory()->count(5)->create(['menu_name' => 'Test Menu', 'menu_price' => 10.50])
        ->each(function($menu): void {
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
                'order_date' => now()->setTime(15, 33)->subDay(),
                'order_total' => 11.00,
                'total_items' => 1,
                'status_id' => setting('completed_order_status')[0],
            ]);

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
                'order_date' => now()->setTime(15, 33)->subDay(),
                'order_total' => 11.00,
                'total_items' => 1,
                'status_id' => setting('canceled_order_status'),
            ]);
        });

    $reportBuilder = new ReportBuilder;
    $reportBuilder->rule_class = MenuItemsReportRule::class;

    $result = $reportBuilder->getTableData(now()->subDays(30)->toDateString(), now()->addDays(3)->toDateString());

    expect($result->count())->toBe(5)
        ->and($result->first())->toBe([
            'menu_item' => 'Test Menu',
            'completed_order_amount' => currency_format(11),
            'completed_order_quantity' => 1,
            'cancelled_order_amount' => currency_format(11),
            'cancelled_order_quantity' => 1,
        ]);
});
