<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\ReportRules;

use Carbon\Carbon;
use Igniter\Cart\Models\Menu;
use Igniter\Coupons\Models\Coupon;
use Igniter\Coupons\Models\CouponHistory;
use Igniter\Flame\Database\Builder;
use Igniter\Flame\Database\Query\Builder as QueryBuilder;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Override;

class DiscountBreakdownRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => lang('igniterlabs.reports::default.text_discount_breakdown_title'),
            'description' => lang('igniterlabs.reports::default.text_discount_breakdown_description'),
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'quantity',
                'label' => lang('igniterlabs.reports::default.label_quantity'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'menu_amount',
                'label' => lang('igniterlabs.reports::default.label_menu_amount'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'total_discount_amount',
                'label' => lang('igniterlabs.reports::default.label_total_discount_amount'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
        ];
    }

    public function defineColumns(): array
    {
        return [
            'coupon_name' => [
                'title' => lang('igniterlabs.reports::default.label_coupon_name'),
            ],
            'menu_item' => [
                'title' => lang('igniterlabs.reports::default.label_menu_item'),
            ],
            'menu_amount' => [
                'title' => lang('igniterlabs.reports::default.label_menu_amount'),
            ],
            'quantity' => [
                'title' => lang('igniterlabs.reports::default.label_quantity'),
            ],
            'total_discount_amount' => [
                'title' => lang('igniterlabs.reports::default.label_total_discount_amount'),
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        $menuTable = DB::getTablePrefix().(new Menu)->getTable();
        $couponMenuTable = DB::getTablePrefix().'igniter_coupon_menus';
        $couponTable = DB::getTablePrefix().(new Coupon)->getTable();
        $couponHistoryTable = DB::getTablePrefix().(new CouponHistory)->getTable();
        $orderMenuTable = DB::getTablePrefix().'order_menus';

        $query = CouponHistory::query();
        $this->locationApplyScope($query);

        $baseQuery = $query
            ->whereBetween('igniter_coupons_history.created_at', [$start, $end])
            ->where('igniter_coupons_history.status', 1)
            ->whereRaw(sprintf('((%s.price * %s.quantity) - %s.subtotal) > 0', $orderMenuTable, $orderMenuTable, $orderMenuTable))
            ->select([
                DB::raw($couponTable.'.name as coupon_name'),
                DB::raw($menuTable.'.menu_name as menu_item'),
                DB::raw($orderMenuTable.'.price as menu_amount'),
                DB::raw($orderMenuTable.'.quantity as quantity'),
                DB::raw(sprintf('SUM((%s.price * %s.quantity) - %s.subtotal) as total_discount_amount', $orderMenuTable, $orderMenuTable, $orderMenuTable)),
            ])
            ->join(DB::raw($couponTable), DB::raw($couponTable.'.coupon_id'), '=', DB::raw($couponHistoryTable.'.coupon_id'))
            ->join(DB::raw($orderMenuTable), DB::raw($orderMenuTable.'.order_id'), '=', DB::raw($couponHistoryTable.'.order_id'))
            ->join(DB::raw($couponMenuTable), function($join) use ($couponMenuTable, $couponTable, $orderMenuTable): void {
                $join->on(DB::raw($couponMenuTable.'.coupon_id'), '=', DB::raw($couponTable.'.coupon_id'))
                    ->on(DB::raw($couponMenuTable.'.menu_id'), '=', DB::raw($orderMenuTable.'.menu_id'));
            })
            ->join(DB::raw($menuTable), DB::raw($menuTable.'.menu_id'), '=', DB::raw($orderMenuTable.'.menu_id'))
            ->groupByRaw($orderMenuTable.'.menu_id,'
                .($couponTable.'.name,')
                .($menuTable.'.menu_name,')
                .($orderMenuTable.'.price,')
                .($orderMenuTable.'.quantity')
            );

        return DB::query()->fromSub($baseQuery, 'discount_breakdown');
    }

    #[Override]
    public function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(fn($report): array => [
            'coupon_name' => $report->coupon_name,
            'menu_item' => $report->menu_item,
            'menu_amount' => $report->menu_amount ? currency_format($report->menu_amount) : null,
            'quantity' => $report->quantity,
            'total_discount_amount' => $report->total_discount_amount ? currency_format($report->total_discount_amount) : 0,
        ]);
    }
}
