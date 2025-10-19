<?php

namespace IgniterLabs\Reports;

use Igniter\System\Classes\BaseExtension;
use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\DashboardWidgets\SmartReports;
use IgniterLabs\Reports\FormWidgets\ReportEditor;
use IgniterLabs\Reports\Listeners\ExtendDashboardCharts;
use IgniterLabs\Reports\ReportRules\DiscountBreakdownRule;
use IgniterLabs\Reports\ReportRules\HourlySalesReportRule;
use IgniterLabs\Reports\ReportRules\MenuItemsReportRule;
use IgniterLabs\Reports\ReportRules\OrderRule;
use IgniterLabs\Reports\ReportRules\OrderTransactionsRule;

class Extension extends BaseExtension
{
    public $singletons = [
        Manager::class,
    ];

    public function boot()
    {
        resolve(ExtendDashboardCharts::class)->registerCharts();
    }

    public function registerFormWidgets(): array
    {
        return [
            ReportEditor::class => [
                'label' => 'Report Editor',
                'code' => 'reporteditor',
            ],
        ];
    }

    public function registerDashboardWidgets(): array
    {
        return [
            SmartReports::class => [
                'label' => 'Smart reports widget',
                'code' => 'smartreports',
            ],
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'tools' => [
                'child' => [
                    'reportbuilder' => [
                        'priority' => 350,
                        'title' => lang('igniterlabs.reports::default.text_title'),
                        'class' => 'reportbuilder',
                        'href' => admin_url('report_builder'),
                        'permission' => 'IgniterLabs.Reports.Manage',
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'IgniterLabs.Reports.Manage' => [
                'label' => lang('igniterlabs.reports::default.text_permission_manage'),
                'group' => lang('igniter::system.permissions.name'),
            ],
        ];
    }

    public function registerReportRules(): array
    {
        return [
            OrderRule::class,
            HourlySalesReportRule::class,
            MenuItemsReportRule::class,
            OrderTransactionsRule::class,
            DiscountBreakdownRule::class,
        ];
    }

    protected function getBestSellingMenuItemsDataset($start, $end) {}

    protected function getWorstSellingMenuItemsDataset($start, $end) {}
}
