<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\Http\Controllers;

use Igniter\System\Classes\ExtensionManager;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\OrderRule;

it('loads report builder index page', function(): void {
    actingAsSuperUser()
        ->get(route('igniterlabs.reports.report_builder'))
        ->assertOk();
});

it('loads create report page', function(): void {
    actingAsSuperUser()
        ->get(route('igniterlabs.reports.report_builder', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit report page', function(): void {
    $report = ReportBuilder::factory()->create();

    actingAsSuperUser()
        ->get(route('igniterlabs.reports.report_builder', ['slug' => 'edit/'.$report->getKey()]))
        ->assertOk();
});

it('creates new report', function(): void {
    app()->instance(ExtensionManager::class, mock(ExtensionManager::class, function($mock): void {
        $mock->shouldReceive('getRegistrationMethodValues')->with('registerReportRules')->andReturn([
            [OrderRule::class],
        ])->once();
    })->makePartial());

    actingAsSuperUser()
        ->post(route('igniterlabs.reports.report_builder', ['slug' => 'create']), [
            'ReportBuilder' => [
                'rule_class' => OrderRule::class,
                'name' => 'Test Report',
                'code' => 'test_report',
                'description' => 'Test Report Title',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();

    $this->assertDatabaseHas('reports_builder', [
        'name' => 'Test Report',
        'code' => 'test_report',
        'rule_class' => OrderRule::class,
    ]);
});

it('updates existing report', function(): void {
    $report = ReportBuilder::factory()->create([
        'name' => 'Original Name',
    ]);

    actingAsSuperUser()
        ->post(route('igniterlabs.reports.report_builder', ['slug' => 'edit/'.$report->getKey()]), [
            'ReportBuilder' => [
                'name' => 'Updated Name',
                'code' => $report->code,
                'columns' => [
                    'column_1',
                    'column_2',
                ],
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();

    expect(ReportBuilder::find($report->getKey())->name)->toBe('Updated Name');
});

it('deletes report', function(): void {
    $report = ReportBuilder::factory()->create();

    actingAsSuperUser()
        ->post(route('igniterlabs.reports.report_builder', ['slug' => 'edit/'.$report->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ])
        ->assertOk();

    expect(ReportBuilder::find($report->getKey()))->toBeNull();
});

it('sets columns after creating report', function(): void {
    app()->instance(ExtensionManager::class, mock(ExtensionManager::class, function($mock): void {
        $mock->shouldReceive('getRegistrationMethodValues')->with('registerReportRules')->andReturn([
            [OrderRule::class],
        ])->once();
    })->makePartial());
    app()->instance(OrderRule::class, mock(OrderRule::class, function($mock): void {
        $mock->shouldReceive('defineColumns')->andReturn([
            'order_id' => ['title' => 'Order ID'],
            'order_total' => ['title' => 'Total'],
        ])->once();
    })->makePartial());

    actingAsSuperUser()
        ->post(route('igniterlabs.reports.report_builder', ['slug' => 'create']), [
            'ReportBuilder' => [
                'name' => 'Test Report',
                'code' => 'test_report',
                'rule_class' => OrderRule::class,
                'title' => 'Test Report Title',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();

    $report = ReportBuilder::where('name', 'Test Report')->first();
    expect($report->columns)->toEqual(['order_id', 'order_total']);
});

