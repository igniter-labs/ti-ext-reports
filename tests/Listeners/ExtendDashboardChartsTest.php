<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\Listeners;

use Igniter\Admin\DashboardWidgets\Charts;
use Igniter\Admin\Http\Controllers\Dashboard;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\User\Models\Customer;
use IgniterLabs\Reports\Listeners\ExtendDashboardCharts;

beforeEach(function(): void {
    $this->listener = new ExtendDashboardCharts;
    $this->startDate = now()->subMonth();
    $this->endDate = now();
    $this->travelTo(now()->subHours(2));
});

afterEach(function(): void {
    $this->travelBack();
});

function setupOrders($customer1, $customer2): void
{
    Order::factory()->count(5)->create([
        'customer_id' => $customer1->getKey(),
        'order_date' => now()->subDays(2),
        'order_total' => 100,
        'status_id' => setting('completed_order_status')[0],
    ]);

    Order::factory()->count(3)->create([
        'customer_id' => $customer2->getKey(),
        'order_date' => now()->subDays(2),
        'order_total' => 200,
        'status_id' => setting('completed_order_status')[0],
    ]);
}

function setupOrdersWithMenus($menu1, $menu2): void
{
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    Order::factory()->count(3)->create([
        'customer_id' => $customer1->getKey(),
        'order_date' => now()->subDays(2),
        'order_total' => 100,
        'status_id' => setting('completed_order_status')[0],
    ])->each(function($order) use ($menu2): void {
        $order->addOrderMenus([
            (object)[
                'id' => $menu2->getKey(),
                'name' => $menu2->menu_name,
                'qty' => 1,
                'price' => $menu2->menu_price,
                'subtotal' => $menu2->menu_price,
                'comment' => '',
                'options' => [],
            ],
        ]);
    });

    Order::factory()->count(5)->create([
        'customer_id' => $customer2->getKey(),
        'order_date' => now()->subDays(2),
        'order_total' => 200,
        'status_id' => setting('completed_order_status')[0],
    ])->each(function($order) use ($menu1): void {
        $order->addOrderMenus([
            (object)[
                'id' => $menu1->getKey(),
                'name' => $menu1->menu_name,
                'qty' => 1,
                'price' => $menu1->menu_price,
                'subtotal' => $menu1->menu_price,
                'comment' => '',
                'options' => [],
            ],
        ]);
    });
}

it('returns registered dashboard charts', function(): void {
    $charts = new class(resolve(Dashboard::class)) extends Charts
    {
        public function testDatasets()
        {
            return $this->listSets();
        }
    };
    $datasets = $charts->testDatasets();

    expect($datasets['top_customers']['datasetFrom'])->toBeCallable()
        ->and($datasets['bottom_customers']['datasetFrom'])->toBeCallable()
        ->and($datasets['best_selling_items']['datasetFrom'])->toBeCallable()
        ->and($datasets['worst_selling_items']['datasetFrom'])->toBeCallable();
});

it('returns correct dataset config for top customers', function(): void {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    setupOrders($customer1, $customer2);

    $result = $this->listener->getDatasetConfig('top_customers', $this->startDate, $this->endDate);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('labels')
        ->and($result)->toHaveKey('datasets')
        ->and($result['labels'][0])->toBe($customer2->full_name)
        ->and($result['labels'][1])->toBe($customer1->full_name)
        ->and($result['datasets'][0])->toHaveKey('backgroundColor')
        ->and($result['datasets'][0])->toHaveKey('data')
        ->and($result['datasets'][0]['data'][0])->toBe(600)
        ->and($result['datasets'][0]['data'][1])->toBe(500);
});

it('returns correct dataset config for bottom customers', function(): void {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    setupOrders($customer1, $customer2);

    $result = $this->listener->getDatasetConfig('bottom_customers', $this->startDate, $this->endDate);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('labels')
        ->and($result)->toHaveKey('datasets')
        ->and($result['labels'][0])->toBe($customer1->full_name)
        ->and($result['labels'][1])->toBe($customer2->full_name)
        ->and($result['datasets'][0])->toHaveKey('backgroundColor')
        ->and($result['datasets'][0])->toHaveKey('data')
        ->and($result['datasets'][0]['data'][0])->toBe(500)
        ->and($result['datasets'][0]['data'][1])->toBe(600);
});

it('returns correct dataset config for best selling items', function(): void {
    $menu1 = Menu::factory()->create(['menu_price' => 22]);
    $menu2 = Menu::factory()->create(['menu_price' => 15]);

    setupOrdersWithMenus($menu1, $menu2);

    $result = $this->listener->getDatasetConfig('best_selling_items', $this->startDate, $this->endDate);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('labels')
        ->and($result)->toHaveKey('datasets')
        ->and($result['labels'][0])->toBe($menu1->menu_name)
        ->and($result['labels'][1])->toBe($menu2->menu_name)
        ->and($result['datasets'][0])->toHaveKey('backgroundColor')
        ->and($result['datasets'][0])->toHaveKey('data')
        ->and($result['datasets'][0]['data'][0])->toBe(5)
        ->and($result['datasets'][0]['data'][1])->toBe(3);
});

it('returns correct dataset config for worst selling items', function(): void {
    $menu1 = Menu::factory()->create(['menu_price' => 22]);
    $menu2 = Menu::factory()->create(['menu_price' => 15]);

    setupOrdersWithMenus($menu1, $menu2);

    $result = $this->listener->getDatasetConfig('worst_selling_items', $this->startDate, $this->endDate);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('labels')
        ->and($result)->toHaveKey('datasets')
        ->and($result['labels'][0])->toBe($menu2->menu_name)
        ->and($result['labels'][1])->toBe($menu1->menu_name)
        ->and($result['datasets'][0])->toHaveKey('backgroundColor')
        ->and($result['datasets'][0])->toHaveKey('data')
        ->and($result['datasets'][0]['data'][0])->toBe(3)
        ->and($result['datasets'][0]['data'][1])->toBe(5);
});
