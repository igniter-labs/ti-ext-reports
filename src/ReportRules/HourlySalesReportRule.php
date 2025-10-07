<?php

namespace IgniterLabs\Reports\ReportRules;

use Carbon\Carbon;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HourlySalesReportRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => lang('igniterlabs.reports::default.text_hourly_sales_report_title'),
            'description' => lang('igniterlabs.reports::default.text_hourly_sales_report_description'),
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'hours',
                'label' => lang('igniterlabs.reports::default.label_hours'),
                'type' => 'string',
                'input' => 'select',
                'multiple' => true,
                'values' => collect(range(0, 23))->mapWithKeys(function ($hour) {
                    $formattedHour = Carbon::createFromTime($hour)->format('g A');
                    return [$formattedHour => $formattedHour];
                })->sortDesc()->toArray(),
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'sales',
                'label' => lang('igniterlabs.reports::default.label_sales'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'covers',
                'label' => lang('igniterlabs.reports::default.label_covers'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'orders',
                'label' => lang('igniterlabs.reports::default.label_orders'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
        ];
    }

    public function defineColumns(): array
    {
        return [
            'hours' => [
                'title' => lang('igniterlabs.reports::default.label_hours')
            ],
            'sales' => [
                'title' => lang('igniterlabs.reports::default.label_sales')
            ],
            'covers' => [
                'title' => lang('igniterlabs.reports::default.label_covers')
            ], // covers represents number of unique menu item categories sold
            'orders' => [
                'title' => lang('igniterlabs.reports::default.label_orders')
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
            ->whereBetween('order_date', [
                Carbon::parse("01/01/2024"), $end])
//                $start, $end])
            ->select([
                DB::raw("SUM($orderTable.order_total) as sales"),
                DB::raw("COUNT($orderTable.order_id) as orders"),
                DB::raw("COUNT(DISTINCT $menusTable.menu_id) as covers"),
                DB::raw("DATE_FORMAT($orderTable.order_time, '%l:00 %p') as hours"),
            ])
            // include 0
            ->join('order_menus', 'order_menus.order_id', '=', 'orders.order_id')
            ->join('menus', 'menus.menu_id', '=', 'order_menus.menu_id')
            ->groupBy(DB::raw("HOUR($orderTable.order_time)"));

        return DB::query()->fromSub($baseQuery, 'hourly_sales_report');
    }

    public static function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(function ($report) {
            return [
                'hours' => $report->hours,
                'sales' => currency_format($report->sales),
                'covers' => $report->covers,
                'orders' => $report->orders,
            ];
        });
    }

    public function getChartDataset(Carbon $start, Carbon $end): array
    {
        $results = $this->getReportQuery($start, $end)->get();

        return [
            'labels' => $results->map(fn($item) => ($item->hours))->all(),
            'datasets' => [
                [
                    'label' => lang('igniterlabs.reports::default.label_sales'),
                    'backgroundColor' => $results->map(fn($item): string => $this->generateBackgroundColor(
                        (string)$item->hours))->all(),
                    'data' => $results->map(fn($item) => (float)$item->sales)->all(),
                ]
            ],
        ];
    }
}
