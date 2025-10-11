<?php

namespace IgniterLabs\Reports\ReportRules;

use Carbon\Carbon;
use Igniter\Admin\Models\Status;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\OrderMenu;
use Igniter\PayRegister\Models\Payment;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrderTransactionsRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => lang('igniterlabs.reports::default.text_order_transactions_title'),
            'description' => lang('igniterlabs.reports::default.text_order_transactions_description'),
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'order_status_id',
                'label' => lang('igniterlabs.reports::default.label_order_status_id'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => true,
                'values' => $this->getStatusList(),
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'payment',
                'label' => lang('igniterlabs.reports::default.label_payment'),
                'type' => 'string',
                'input' => 'select',
                'multiple' => true,
                'values' => $this->getPaymentList(),
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'menu_id',
                'label' => lang('igniterlabs.reports::default.label_menu_id'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => true,
                'values' => $this->getMenuList(),
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'quantity',
                'label' => lang('igniterlabs.reports::default.label_quantity'),
                'type' => 'integer',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'amount',
                'label' => lang('igniterlabs.reports::default.label_amount'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'date',
                'label' => lang('igniterlabs.reports::default.label_date'),
                'type' => 'date',
                'input' => 'datepicker',
                'validation' => [
                    'format' => 'YYYY/MM/DD',
                ],
                'operators' => $this->getDateOperators(),
            ]
        ];
    }

    public function defineColumns(): array
    {
        return [
            'order_status' => [
                'title' => lang('igniterlabs.reports::default.label_order_status')
            ],
            'date' => [
                'title' => lang('igniterlabs.reports::default.label_date')
            ],
            'order_id' => [
                'title' => lang('igniterlabs.reports::default.label_order_id')
            ],
            'menu_id' => [
                'title' => lang('igniterlabs.reports::default.label_menu_id')
            ],
            'quantity' => [
                'title' => lang('igniterlabs.reports::default.label_quantity')
            ],
            'menu_item' => [
                'title' => lang('igniterlabs.reports::default.label_menu_item')
            ],
            'amount' => [
                'title' => lang('igniterlabs.reports::default.label_amount')
            ],
            'payment' => [
                'title' => lang('igniterlabs.reports::default.label_payment')
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        $orderTable = DB::getTablePrefix() . (new Order)->getTable();
        $orderMenuTable = DB::getTablePrefix() . (new OrderMenu)->getTable();
        $statusTable = DB::getTablePrefix() . (new Status)->getTable();
        $paymentsTable = DB::getTablePrefix() . (new Payment)->getTable();
        $query = OrderMenu::query();
        $this->locationApplyScope($query);

        $baseQuery = $query
            ->whereBetween('order_date', [Carbon::parse("01/01/2024"), $end])
            ->select([
                DB::raw("$orderMenuTable.name as menu_item"),
                DB::raw("$orderMenuTable.order_id as order_id"),
                DB::raw("DATE($orderTable.order_date) as date"),
                DB::raw("SUM($orderMenuTable.quantity) as quantity"),
                DB::raw("SUM($orderMenuTable.subtotal) as amount"),
                DB::raw("$orderTable.status_id as order_status_id"),
                DB::raw("$statusTable.status_name as order_status"),
                DB::raw("$orderMenuTable.menu_id as menu_id"),
                DB::raw("$orderTable.payment as payment"),
                DB::raw("$paymentsTable.name as payment_name")

            ])
            ->leftJoin('orders', 'orders.order_id', '=', 'order_menus.order_id')
            ->leftJoin('statuses', 'statuses.status_id', '=', 'orders.status_id')
            ->leftJoin('payments', 'payments.code', '=', 'orders.payment')
            ->groupBy('order_menus.order_menu_id');

        return DB::query()->fromSub($baseQuery, 'order_transactions');
    }

    public static function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(function ($report) {
            return [
                'menu_item' => $report->menu_item,
                'order_id' => $report->order_id,
                'menu_id' => $report->menu_id,
                'date' => $report->date,
                'quantity' => $report->quantity,
                'amount' => currency_format($report->amount),
                'order_status' => $report->order_status,
                'payment' => $report->payment_name,
            ];
        });
    }

    protected function getStatusList(): array
    {
        return Status::getDropdownOptionsForOrder()->all();
    }

    protected function getPaymentList(): array
    {
        return Payment::listDropdownOptions()->all();
    }

    protected function getMenuList(): array
    {
        return Menu::getDropdownOptions()->all();
    }
}
