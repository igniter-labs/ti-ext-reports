<?php

namespace IgniterLabs\Reports;

use Igniter\Flame\Database\Builder;
use Igniter\System\Classes\BaseExtension;
use Igniter\User\Models\Customer;
use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\DashboardWidgets\SmartReports;
use IgniterLabs\Reports\FormWidgets\ReportEditor;
use IgniterLabs\Reports\ReportRules\CustomerRule;
use IgniterLabs\Reports\ReportRules\OrderRule;
use Illuminate\Support\Facades\DB;

class Extension extends BaseExtension
{
    public $singletons = [
        Manager::class,
    ];

    public function boot() {}

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

    public function registerReportRules()
    {
        return [
            CustomerRule::class,
            OrderRule::class,
        ];
    }

    protected function getTopCustomersDataset($start, $end): array
    {
        $dataset = $this->getDataset($start, $end, function(Builder $query): void {
            $customerTable = DB::getTablePrefix().(new Customer)->getTable();
            $query
                ->select(
                    DB::raw(sprintf("CONCAT(%s.first_name, ' ', %s.last_name) as label", $customerTable, $customerTable)),
                    DB::raw('SUM(order_total) as count'),
                )
                ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->groupBy('customers.customer_id')
                ->orderBy('count', 'desc')
                ->limit(10);
        });

        return $dataset;
    }

    protected function getBottomCustomersDataset($start, $end): array
    {
        return $this->getDataset($start, $end, function(Builder $query): void {
            $query->select('customers.name as label', DB::raw('SUM(order_total) as count'))
                ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->groupBy('customers.customer_id')
                ->orderBy('count')
                ->limit(10);
        });
    }

    protected function getBestSellingMenuItemsDataset($start, $end) {}

    protected function getWorstSellingMenuItemsDataset($start, $end) {}
}
