<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests;

use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\DashboardWidgets\SmartReports;
use IgniterLabs\Reports\Extension;
use IgniterLabs\Reports\FormWidgets\ReportEditor;
use IgniterLabs\Reports\ReportRules\DiscountBreakdownRule;
use IgniterLabs\Reports\ReportRules\HourlySalesReportRule;
use IgniterLabs\Reports\ReportRules\MenuItemsReportRule;
use IgniterLabs\Reports\ReportRules\OrderRule;
use IgniterLabs\Reports\ReportRules\OrderTransactionsRule;

it('registers manager singleton', function(): void {
    expect((new Extension(app()))->singletons)->toBe([
        Manager::class,
    ]);
});

it('registers form widgets correctly', function(): void {
    $formWidgets = (new Extension(app()))->registerFormWidgets();

    expect($formWidgets)->toHaveKey(ReportEditor::class)
        ->and($formWidgets[ReportEditor::class]['label'])->toBe('Report Editor')
        ->and($formWidgets[ReportEditor::class]['code'])->toBe('reporteditor');
});

it('registers dashboard widgets correctly', function(): void {
    $dashboardWidgets = (new Extension(app()))->registerDashboardWidgets();

    expect($dashboardWidgets)->toHaveKey(SmartReports::class)
        ->and($dashboardWidgets[SmartReports::class]['label'])->toBe('Smart reports widget')
        ->and($dashboardWidgets[SmartReports::class]['code'])->toBe('smartreports');
});

it('registers navigation with correct attributes', function(): void {
    $navigation = (new Extension(app()))->registerNavigation();

    expect($navigation)->toHaveKey('tools')
        ->and($navigation['tools']['child'])->toHaveKey('reportbuilder')
        ->and($navigation['tools']['child']['reportbuilder']['priority'])->toBe(350)
        ->and($navigation['tools']['child']['reportbuilder']['title'])->toBe(lang('igniterlabs.reports::default.text_title'))
        ->and($navigation['tools']['child']['reportbuilder']['class'])->toBe('reportbuilder')
        ->and($navigation['tools']['child']['reportbuilder']['href'])->toBe(admin_url('report_builder'))
        ->and($navigation['tools']['child']['reportbuilder']['permission'])->toBe('IgniterLabs.Reports.Manage');
});

it('registers permissions with correct attributes', function(): void {
    $permissions = (new Extension(app()))->registerPermissions();

    expect($permissions)->toHaveKey('IgniterLabs.Reports.Manage')
        ->and($permissions['IgniterLabs.Reports.Manage']['label'])->toBe(lang('igniterlabs.reports::default.text_permission_manage'))
        ->and($permissions['IgniterLabs.Reports.Manage']['group'])->toBe(lang('igniter::system.permissions.name'));
});

it('registers report rules correctly', function(): void {
    $reportRules = (new Extension(app()))->registerReportRules();

    expect($reportRules)->toContain(
        OrderRule::class,
        HourlySalesReportRule::class,
        MenuItemsReportRule::class,
        OrderTransactionsRule::class,
        DiscountBreakdownRule::class
    );
});
