<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\DashboardWidgets;

use Igniter\Admin\FormWidgets\DataTable;
use Igniter\Admin\Http\Controllers\Dashboard;
use Igniter\Cart\Models\Order;
use Igniter\System\Facades\Assets;
use IgniterLabs\Reports\DashboardWidgets\SmartReports;
use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\OrderRule;

beforeEach(function(): void {
    $this->reportBuilder = ReportBuilder::factory()->create();
    $this->smartReportWidget = new SmartReports(resolve(Dashboard::class), [
        'report' => $this->reportBuilder->getKey(),
        'startDate' => now()->subDays(7)->toDateTimeString(),
        'endDate' => now()->toDateTimeString(),
    ]);
});

it('prepares vars', function(): void {
    $this->smartReportWidget->prepareVars();

    expect($this->smartReportWidget->vars['widgetTitle'])->toBe($this->reportBuilder->name)
        ->and($this->smartReportWidget->vars['widget'])->toBeInstanceOf(DataTable::class);
});

it('loads assets correctly', function(): void {
    Assets::shouldReceive('addJs')->once()->with('js/smartreports.js', 'smartreports-js');
    Assets::shouldReceive('addCss')->once()->with('widgets/table.css', 'table-css');

    Assets::shouldReceive('addCss')->once()->with('css/smartreports.css', 'smartreports-css');
    Assets::shouldReceive('addJs')->once()->with('widgets/table.js', 'table-js');

    $this->smartReportWidget->assetPath = [];

    $this->smartReportWidget->loadAssets();
});

it('renders smart reports widget', function(): void {
    expect($this->smartReportWidget->render())->toBeString();
});

it('loads reports from database', function(): void {
    Order::factory()->count(5)->create([
        'order_date' => now()->subDays(3)->toDateString(),
    ]);
    $this->smartReportWidget->prepareVars();

    ReportBuilder::factory()->count(3)->create([
        'name' => 'Test Report',
        'rule_class' => OrderRule::class,
    ]);

    $widget = $this->smartReportWidget->vars['widget'];
    $reports = $widget->getTable()->fireEvent('table.getRecords', [0, 6]);

    expect($reports[0])->toHaveCount(5);
});
