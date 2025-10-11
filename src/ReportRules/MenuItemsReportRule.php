<?php

namespace IgniterLabs\Reports\ReportRules;

use Carbon\Carbon;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class MenuItemsReportRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => lang('igniterlabs.reports::default.text_menu_items_report_title'),
            'description' => lang('igniterlabs.reports::default.text_menu_items_report_description'),
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'completed_order_amount',
                'label' => lang('igniterlabs.reports::default.label_completed_order_amount'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'completed_order_quantity',
                'label' => lang('igniterlabs.reports::default.label_completed_order_quantity'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'canceled_order_amount',
                'label' => lang('igniterlabs.reports::default.label_cancelled_order_amount'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'canceled_order_quantity',
                'label' => lang('igniterlabs.reports::default.label_cancelled_order_quantity'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
        ];
    }

    public function defineColumns(): array
    {
        return [
            'menu_item' => [
                'title' => lang('igniterlabs.reports::default.label_menu_item')
            ],
            'completed_order_amount' => [
                'title' => lang('igniterlabs.reports::default.label_completed_order_amount')
            ],
            'completed_order_quantity' => [
                'title' => lang('igniterlabs.reports::default.label_completed_order_quantity')
            ],
            'cancelled_order_amount' => [
                'title' => lang('igniterlabs.reports::default.label_cancelled_order_amount')
            ],
            'cancelled_order_quantity' => [
                'title' => lang('igniterlabs.reports::default.label_cancelled_order_quantity')
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        $orderTable = DB::getTablePrefix() . (new Order)->getTable();
        $menusTable = DB::getTablePrefix() . (new Menu)->getTable();
        $query = Order::query();
        $this->locationApplyScope($query);


        $baseQuery = $query
            ->whereBetween('order_date', [$start, $end])
            ->select([
                DB::raw("$menusTable.menu_name as menu_item"),
                DB::raw(
                    $this->getSumSqlByOrderStatus(
                        $orderTable, "order_total", setting('completed_order_status')
                    ) . " as completed_order_amount"),
                DB::raw(
                    $this->getSumSqlByOrderStatus(
                        $orderTable, "total_items", setting('completed_order_status')
                    ) . " as completed_order_quantity",
                ),
                DB::raw(
                    $this->getSumSqlByOrderStatus(
                        $orderTable, "order_total", setting('canceled_order_status')
                    ) . " as cancelled_order_amount",
                ),
                DB::raw(
                    $this->getSumSqlByOrderStatus(
                        $orderTable, "total_items", setting('canceled_order_status')
                    ) . " as cancelled_order_quantity",
                ),
            ])
            ->join('order_menus', 'order_menus.order_id', '=', 'orders.order_id')
            ->join('menus', 'menus.menu_id', '=', 'order_menus.menu_id')
            ->groupBy('menus.menu_id');

        return DB::query()->fromSub($baseQuery, 'menu_items_report');
    }

    protected function getSumSqlByOrderStatus(string $orderTable, string $field, array|int $statusIds): string
    {
        if (is_array($statusIds)) {
            $statusCondition = sprintf("%s.status_id IN (%s)", $orderTable, implode(',', $statusIds));
        } else {
            $statusCondition = sprintf("%s.status_id = %d", $orderTable, $statusIds);
        }

        return "SUM(CASE WHEN $statusCondition THEN $orderTable.$field ELSE 0 END)";
    }

    public static function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(function ($report) {
            return [
                'menu_item' => $report->menu_item,
                'completed_order_amount' => currency_format($report->completed_order_amount),
                'completed_order_quantity' => (int)$report->completed_order_quantity,
                'cancelled_order_amount' => currency_format($report->cancelled_order_amount),
                'cancelled_order_quantity' => (int)$report->cancelled_order_quantity,
            ];
        });
    }
}
