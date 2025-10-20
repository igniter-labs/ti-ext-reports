<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\Classes;

use Igniter\System\Classes\ExtensionManager;
use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\ReportRules\HourlySalesReportRule;
use IgniterLabs\Reports\ReportRules\OrderRule;
use Illuminate\Support\Collection;

it('loads rules collection', function(): void {
    app()->instance(ExtensionManager::class, mock(ExtensionManager::class, function($mock): void {
        $mock->shouldReceive('getRegistrationMethodValues')->with('registerReportRules')->andReturn([
            [OrderRule::class],
        ])->once();
    }));

    $rules = resolve(Manager::class)->loadRules();

    expect($rules)->toHaveKey(OrderRule::class)
        ->and($rules[OrderRule::class])->toBeInstanceOf(OrderRule::class);
});

it('loads rule filters', function(): void {
    app()->instance(ExtensionManager::class, mock(ExtensionManager::class, function($mock): void {
        $mock->shouldReceive('getRegistrationMethodValues')->with('registerReportRules')->andReturn([
            [OrderRule::class, HourlySalesReportRule::class],
        ])->once();
    }));
    app()->instance(OrderRule::class, mock(OrderRule::class, function($mock): void {
        $mock->shouldReceive('defineFilters')->andReturn([
            'status' => 'Order Status',
        ])->once();
    }));
    app()->instance(HourlySalesReportRule::class, mock(HourlySalesReportRule::class, function($mock): void {
        $mock->shouldReceive('defineFilters')->andReturn([
            'hour' => 'Hour of Day',
        ])->once();
    }));

    $filters = resolve(Manager::class)->loadRuleFilters();

    expect($filters)->toHaveKeys([OrderRule::class, HourlySalesReportRule::class])
        ->and($filters[OrderRule::class])->toBeInstanceOf(Collection::class);
});

it('gets specific rule by class name', function(): void {
    app()->instance(ExtensionManager::class, mock(ExtensionManager::class, function($mock): void {
        $mock->shouldReceive('getRegistrationMethodValues')->with('registerReportRules')->andReturn([
            [OrderRule::class],
        ])->once();
    }));

    $rule = resolve(Manager::class)->getRule(OrderRule::class);

    expect($rule)->toBeInstanceOf(OrderRule::class);
});
