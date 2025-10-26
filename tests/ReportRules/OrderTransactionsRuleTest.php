<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\ReportRules;

use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\PayRegister\Models\Payment;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\OrderTransactionsRule;

it('returns correct rule details', function(): void {
    $details = resolve(OrderTransactionsRule::class)->ruleDetails();

    expect($details)->toHaveKey('name')
        ->and($details)->toHaveKey('description')
        ->and($details['name'])->toBe('Order Transactions')
        ->and($details['description'])->toBe('Displays a list of order transactions.');
});

it('defines filters correctly', function(): void {
    $filters = resolve(OrderTransactionsRule::class)->defineFilters();

    expect($filters)->toBeArray()
        ->and($filters)->toHaveCount(6)

        ->and($filters[0]['id'])->toBe('order_status_id')
        ->and($filters[0]['type'])->toBe('integer')
        ->and($filters[0]['input'])->toBe('select')
        ->and($filters[0]['multiple'])->toBeTrue()
        ->and($filters[0]['values'])->toBeArray()
        ->and($filters[0]['operators'])->toHaveCount(10)

        ->and($filters[1]['id'])->toBe('payment')
        ->and($filters[1]['type'])->toBe('string')
        ->and($filters[1]['input'])->toBe('select')
        ->and($filters[1]['multiple'])->toBeTrue()
        ->and($filters[1]['values'])->toBeArray()
        ->and($filters[1]['operators'])->toHaveCount(12)

        ->and($filters[2]['id'])->toBe('menu_id')
        ->and($filters[2]['type'])->toBe('integer')
        ->and($filters[2]['input'])->toBe('select')
        ->and($filters[2]['multiple'])->toBeTrue()
        ->and($filters[2]['values'])->toBeArray()
        ->and($filters[2]['operators'])->toHaveCount(10)

        ->and($filters[3]['id'])->toBe('quantity')
        ->and($filters[3]['type'])->toBe('integer')
        ->and($filters[3]['input'])->toBe('number')
        ->and($filters[3]['operators'])->toHaveCount(10)

        ->and($filters[4]['id'])->toBe('amount')
        ->and($filters[4]['type'])->toBe('double')
        ->and($filters[4]['input'])->toBe('number')
        ->and($filters[4]['operators'])->toHaveCount(10)

        ->and($filters[5]['id'])->toBe('date')
        ->and($filters[5]['type'])->toBe('date')
        ->and($filters[5]['input'])->toBe('datepicker')
        ->and($filters[5]['validation'])->toHaveKey('format')
        ->and($filters[5]['operators'])->toHaveCount(8);
});

it('defines columns correctly', function(): void {
    $columns = resolve(OrderTransactionsRule::class)->defineColumns();

    expect($columns)->toHaveKeys([
        'order_status',
        'date',
        'order_id',
        'menu_id',
        'quantity',
        'menu_item',
        'amount',
        'payment',
    ]);
});

it('gets report data with date range correctly', function(): void {
    $menu = Menu::factory()->create(['menu_name' => 'Test Menu', 'menu_price' => 10.50]);
    $payment = Payment::factory()->create(['name' => 'Cash']);

    $order = Order::factory()->afterCreating(function(Order $order) use ($menu): void {
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
        'order_date' => now()->subDays(10),
        'order_total' => 21.00,
        'status_id' => setting('completed_order_status')[0],
        'payment' => $payment->code,
    ]);

    $reportBuilder = new ReportBuilder;
    $reportBuilder->rule_class = OrderTransactionsRule::class;

    $result = $reportBuilder->getTableData(now()->subDays(30)->toDateString(), now()->addDays(3)->toDateString());

    expect($result->count())->toBe(1)
        ->and($result->first())->toBe([
            'menu_item' => 'Test Menu',
            'order_id' => $order->order_id,
            'menu_id' => $menu->menu_id,
            'date' => now()->subDays(10)->toDateString(),
            'quantity' => '2',
            'amount' => currency_format(21),
            'order_status' => 'Completed',
            'payment' => 'Cash',
        ]);
});
