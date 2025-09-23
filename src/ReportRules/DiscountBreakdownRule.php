<?php

namespace IgniterLabs\Reports\ReportRules;

use Carbon\Carbon;
use Igniter\Cart\Models\Menu;
use Igniter\Coupons\Models\Coupon;
use Igniter\Coupons\Models\CouponHistory;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
                'title' => lang('igniterlabs.reports::default.label_coupon_name')
            ],
            'menu_item' => [
                'title' => lang('igniterlabs.reports::default.label_menu_item')
            ],
            'menu_amount' => [
                'title' => lang('igniterlabs.reports::default.label_menu_amount')
            ],
            'quantity' => [
                'title' => lang('igniterlabs.reports::default.label_quantity')
            ],
            'total_discount_amount' => [
                'title' => lang('igniterlabs.reports::default.label_total_discount_amount')
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder
    {
        $menuTable = DB::getTablePrefix() . (new Menu)->getTable();
        $couponMenuTable = DB::getTablePrefix() . 'igniter_coupon_menus';
        $couponTable = DB::getTablePrefix() . (new Coupon)->getTable();
        $couponHistoryTable = DB::getTablePrefix() . (new CouponHistory)->getTable();

        $query = CouponHistory::query();
        $this->locationApplyScope($query);

        return $query
            ->whereBetween('igniter_coupons_history.created_at', [
                Carbon::parse("01/01/2024"),
//                $start,
                $end])
            ->select([
                DB::raw("$couponTable.name as coupon_name"),
                DB::raw("$menuTable.menu_name as menu_name"),
                DB::raw("$menuTable.menu_price as menu_amount"),
                DB::raw("COUNT($couponMenuTable.menu_id) as quantity"),
                DB::raw("SUM($couponHistoryTable.amount) as total_discount_amount"),
            ])
            ->leftJoin(DB::raw($couponTable), DB::raw("$couponTable.coupon_id"), '=', DB::raw("$couponTable.coupon_id"))
            ->leftJoin(DB::raw($couponMenuTable), DB::raw("$couponMenuTable.coupon_id"), '=', DB::raw("$couponHistoryTable.coupon_id"))
            ->leftJoin("menus", 'menus.menu_id', '=', DB::raw("$couponMenuTable.menu_id"))
            ->groupBy('igniter_coupons_history.coupon_history_id');
    }

    public function getTableData(Carbon $start, Carbon $end, int $pageLimit = 5, $currentPage = null): LengthAwarePaginator
    {
        return parent::getTableData($start, $end, $pageLimit, $currentPage)->through(function ($report) {
            return [
                'coupon_name' => $report->coupon_name,
                'menu_name' => $report->menu_name,
                'menu_amount' => $report->menu_amount ? currency_format($report->menu_amount) : null,
                'quantity' => $report->quantity,
                'total_discount_amount' => currency_format($report->total_discount_amount),
            ];
        });
    }

}
