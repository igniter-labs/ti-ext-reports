<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\ReportRules;

use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\OrderRule;
use Illuminate\Support\Carbon;

it('returns correct rule details', function(): void {
    $details = resolve(OrderRule::class)->ruleDetails();

    expect($details)->toHaveKey('name')
        ->and($details)->toHaveKey('description')
        ->and($details['name'])->toBe('Orders')
        ->and($details['description'])->toBe('Filter orders based on various rules');
});

it('defines filters correctly', function(): void {
    $filters = resolve(OrderRule::class)->defineFilters();

    expect($filters)->toBeArray()
        ->and($filters[0]['id'])->toBe('location_id')
        ->and($filters[0]['type'])->toBe('integer')
        ->and($filters[0]['input'])->toBe('select')
        ->and($filters[0]['multiple'])->toBeTrue()
        ->and($filters[0]['values'])->toBeArray()
        ->and($filters[0]['operators'])->toHaveCount(4)

        ->and($filters[1]['id'])->toBe('customer_name')
        ->and($filters[1]['type'])->toBe('string')
        ->and($filters[1]['input'])->toBe('text')
        ->and($filters[1]['operators'])->toHaveCount(12)

        ->and($filters[2]['id'])->toBe('email')
        ->and($filters[2]['type'])->toBe('string')
        ->and($filters[2]['input'])->toBe('text')
        ->and($filters[2]['operators'])->toHaveCount(12)

        ->and($filters[3]['id'])->toBe('customer_group')
        ->and($filters[3]['type'])->toBe('integer')
        ->and($filters[3]['input'])->toBe('select')
        ->and($filters[3]['values'])->toHaveCount(1)
        ->and($filters[3]['operators'])->toHaveCount(8)

        ->and($filters[4]['id'])->toBe('date_added')
        ->and($filters[4]['type'])->toBe('date')
        ->and($filters[4]['input'])->toBe('datepicker')
        ->and($filters[4]['validation'])->toBe(['format' => 'YYYY/MM/DD'])
        ->and($filters[4]['operators'])->toHaveCount(6)

        ->and($filters[5]['id'])->toBe('order_date_relative')
        ->and($filters[5]['input'])->toBe('select')
        ->and($filters[5]['values'])->toHaveCount(5)
        ->and($filters[5]['operators'])->toHaveCount(6)

        ->and($filters[6]['id'])->toBe('order_date')
        ->and($filters[6]['type'])->toBe('date')
        ->and($filters[6]['input'])->toBe('datepicker')
        ->and($filters[6]['validation'])->toBe(['format' => 'YYYY/MM/DD'])
        ->and($filters[6]['operators'])->toHaveCount(6)

        ->and($filters[7]['id'])->toBe('order_time')
        ->and($filters[7]['type'])->toBe('time')
        ->and($filters[7]['input'])->toBe('datepicker')
        ->and($filters[7]['validation'])->toBe(['format' => 'HH:mm'])
        ->and($filters[7]['operators'])->toHaveCount(6)

        ->and($filters[8]['id'])->toBe('order_type')
        ->and($filters[8]['type'])->toBe('string')
        ->and($filters[8]['input'])->toBe('select')
        ->and($filters[8]['values'])->toHaveCount(2)
        ->and($filters[8]['operators'])->toHaveCount(2)

        ->and($filters[9]['id'])->toBe('delivery_address')
        ->and($filters[9]['type'])->toBe('string')
        ->and($filters[9]['input'])->toBe('text')
        ->and($filters[9]['operators'])->toHaveCount(8)

        ->and($filters[10]['id'])->toBe('categories')
        ->and($filters[10]['type'])->toBe('string')
        ->and($filters[10]['input'])->toBe('select')
        ->and($filters[10]['multiple'])->toBeTrue()
        ->and($filters[10]['values'])->toHaveCount(8)
        ->and($filters[10]['operators'])->toHaveCount(4)

        ->and($filters[11]['id'])->toBe('menus')
        ->and($filters[11]['type'])->toBe('string')
        ->and($filters[11]['input'])->toBe('select')
        ->and($filters[11]['values'])->toHaveCount(12)
        ->and($filters[11]['operators'])->toHaveCount(4);
});

it('defines columns correctly', function(): void {
    $columns = resolve(OrderRule::class)->defineColumns();

    expect($columns)->toHaveKeys(['customer_name', 'email', 'order_total', 'order_date', 'order_type']);
});

it('gets report data with date range correctly', function(): void {
    $location = Location::factory()->create(['location_name' => 'Test Location']);
    $customer = Customer::factory()->create();
    Order::factory()->count(5)->create([
        'location_id' => $location->getKey(),
        'customer_id' => $customer->getKey(),
        'first_name' => $customer->first_name,
        'last_name' => $customer->last_name,
        'email' => $customer->email,
        'order_date' => Carbon::now()->subDays(2)->toDateString(),
        'order_time' => '14:30:00',
        'order_type' => 'delivery',
        'order_total' => 50.00,
    ]);

    $reportBuilder = new ReportBuilder;
    $reportBuilder->rule_class = OrderRule::class;

    $result = $reportBuilder->getTableData(now()->subDays(30)->toDateString(), now()->addDays(3)->toDateString());

    expect($result->count())->toBe(5)
        ->and($result->first())->toBe([
            'customer_name' => $customer->full_name,
            'email' => $customer->email,
            'order_total' => currency_format(50),
            'order_date' => Carbon::now()->subDays(2)->setTime(14, 30)->isoFormat(lang('system::lang.moment.date_time_format')),
            'order_type' => 'Delivery',
        ]);
});
