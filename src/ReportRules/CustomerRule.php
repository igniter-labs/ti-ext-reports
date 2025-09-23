<?php

namespace IgniterLabs\Reports\ReportRules;

use Igniter\Cart\Models\Order;
use Igniter\User\Models\Customer;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => 'Customers',
            'description' => 'Filter customers based on various rules',
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'first_name',
                'label' => lang('igniterlabs.reports::default.label_name'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'email',
                'label' => lang('igniterlabs.reports::default.label_email'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'address',
                'label' => lang('igniterlabs.reports::default.label_address'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'ip_address',
                'label' => lang('igniterlabs.reports::default.label_ip_address'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'telephone',
                'label' => lang('igniterlabs.reports::default.label_telephone'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getTextOperators(),
            ],
            [
                'id' => 'order_count',
                'label' => lang('igniterlabs.reports::default.label_order_count'),
                'type' => 'double',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'order_total',
                'label' => lang('igniterlabs.reports::default.label_order_total'),
                'type' => 'string',
                'input' => 'number',
                'operators' => $this->getNumericOperators(),
            ],
            [
                'id' => 'status',
                'label' => lang('igniterlabs.reports::default.label_order_total'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'enabled' => lang('admin::lang.text_enabled'),
                    'disabled' => lang('admin::lang.text_disabled'),
                ],
                'operators' => $this->getStatusOperators(),
            ],
            [
                'id' => 'date_added',
                'label' => lang('igniterlabs.reports::default.label_date_added'),
                'type' => 'date',
                'input' => 'datepicker',
                'validation' => [
                    'format' => 'YYYY/MM/DD',
                ],
                'operators' => $this->getDateOperators(),
            ],
            [
                'id' => 'date_relative',
                'label' => lang('igniterlabs.reports::default.label_date_relative'),
                'input' => 'select',
                'values' => [
                    '7' => lang('igniterlabs.reports::default.text_7_days'),
                    '14' => lang('igniterlabs.reports::default.text_14_days'),
                    '30' => lang('igniterlabs.reports::default.text_30_days'),
                    '90' => lang('igniterlabs.reports::default.text_90_days'),
                    '365' => lang('igniterlabs.reports::default.text_365_days'),
                ],
                'operators' => $this->getDateOperators(),
            ],
            [
                'id' => 'order_date',
                'label' => lang('igniterlabs.reports::default.label_order_date'),
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
            'full_name' => [
                'title' => lang('igniterlabs.reports::default.label_name'),
            ],
            'first_name' => [
                'title' => lang('igniterlabs.reports::default.label_first_name'),
            ],
            'last_name' => [
                'title' => lang('igniterlabs.reports::default.label_last_name'),
            ],
            'email' => [
                'title' => lang('igniterlabs.reports::default.label_email'),
            ],
            'telephone' => [
                'title' => lang('igniterlabs.reports::default.label_telephone'),
            ],
            'address' => [
                'title' => lang('igniterlabs.reports::default.label_address'),
            ],
            'ip_address' => [
                'title' => lang('igniterlabs.reports::default.label_ip_address'),
            ],
            'status' => [
                'title' => lang('igniterlabs.reports::default.label_status'),
            ],
            'order_count' => [
                'title' => lang('igniterlabs.reports::default.label_order_count'),
            ],
            'order_total' => [
                'title' => lang('igniterlabs.reports::default.label_order_total'),
            ],
            'date_added' => [
                'title' => lang('igniterlabs.reports::default.label_date_added'),
                'type' => 'datetime',
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder
    {
        $customerTable = DB::getTablePrefix() . (new Customer)->getTable();
        $orderTable = DB::getTablePrefix() . (new Order)->getTable();
        return Customer::query()
            ->whereBetween('orders.created_at', [$start, $end])
            ->select([
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.created_at as date_added',
                'orders.created_at as order_date',
                DB::raw(sprintf('COUNT(%s.order_id) as order_count', $orderTable)),
                DB::raw(sprintf('COALESCE(SUM(%s.order_total), 0) as order_total', $orderTable)),
                DB::raw(sprintf("CONCAT(%s.first_name, ' ', %s.last_name) as full_name", $customerTable, $customerTable))
            ])
            ->leftJoin('orders', 'orders.customer_id', '=', 'customers.customer_id')
            ->groupBy('customers.customer_id');
    }

    protected function getCustomerNameOperators(): array
    {
        return [
            'equal', 'not_equal',
            'begins_with', 'not_begins_with',
            'contains', 'not_contains',
            'ends_with', 'not_ends_with',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }

    protected function getEmailOperators(): array
    {
        return [
            'equal', 'not_equal',
            'begins_with', 'not_begins_with',
            'contains', 'not_contains',
            'ends_with', 'not_ends_with',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }

    protected function getOrderCountOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }

    protected function getStatusOperators(): array
    {
        return [
            'equal', 'not_equal',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }
}
