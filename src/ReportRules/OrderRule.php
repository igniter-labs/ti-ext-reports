<?php

namespace IgniterLabs\Reports\ReportRules;

use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\User\Facades\AdminAuth;
use Igniter\User\Models\CustomerGroup;
use IgniterLabs\Reports\Classes\BaseRule;
use Igniter\Flame\Database\Builder;
use Igniter\Flame\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class OrderRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => 'Orders',
            'description' => 'Filter orders based on various rules',
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'location_id',
                'label' => lang('igniterlabs.reports::default.label_location'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => true,
                'values' => $this->getLocationIdOptions(),
                'operators' => $this->getLocationIdOperators(),
            ],
            [
                'id' => 'customer_name',
                'label' => lang('igniterlabs.reports::default.label_customer_name'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getCustomerNameOperators(),
            ],
            [
                'id' => 'email',
                'label' => lang('igniterlabs.reports::default.label_email'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getEmailOperators(),
            ],
            [
                'id' => 'customer_group',
                'label' => lang('igniterlabs.reports::default.label_customer_group'),
                'type' => 'integer',
                'input' => 'select',
                'values' => CustomerGroup::getDropdownOptions(),
                'operators' => $this->getCustomerGroupOperators(),
            ],
            [
                'id' => 'date_added',
                'label' => lang('igniterlabs.reports::default.label_date_added'),
                'type' => 'date',
                'input' => 'datepicker',
                'validation' => [
                    'format' => 'YYYY/MM/DD',
                ],
                'operators' => $this->getDateAddedOperators(),
            ],
            [
                'id' => 'order_date_relative',
                'label' => lang('igniterlabs.reports::default.label_order_date_relative'),
                'input' => 'select',
                'values' => [
                    '7' => lang('igniterlabs.reports::default.text_7_days'),
                    '14' => lang('igniterlabs.reports::default.text_14_days'),
                    '30' => lang('igniterlabs.reports::default.text_30_days'),
                    '90' => lang('igniterlabs.reports::default.text_90_days'),
                    '365' => lang('igniterlabs.reports::default.text_365_days'),
                ],
                'operators' => $this->getOrderDateRelativeOperators(),
            ],
            [
                'id' => 'order_date',
                'label' => lang('igniterlabs.reports::default.label_order_date'),
                'type' => 'date',
                'input' => 'datepicker',
                'validation' => [
                    'format' => 'YYYY/MM/DD',
                ],
                'operators' => $this->getOrderDateOperators(),
            ],
            [
                'id' => 'order_time',
                'label' => lang('igniterlabs.reports::default.label_order_time'),
                'type' => 'time',
                'input' => 'datepicker',
                'validation' => [
                    'format' => 'HH:mm',
                ],
                'operators' => $this->getOrderDateOperators(),
            ],
            [
                'id' => 'order_type',
                'label' => lang('igniterlabs.reports::default.label_order_type'),
                'type' => 'string',
                'input' => 'select',
                'multiple' => true,
                'values' => LocationModel::getOrderTypeOptions()->mapWithKeys(fn($name, $code) => [$code => lang($name)])->all(),
                'operators' => $this->getOrderTypeOperators(),
            ],
            [
                'id' => 'delivery_address',
                'label' => lang('igniterlabs.reports::default.label_delivery_address'),
                'type' => 'string',
                'input' => 'text',
                'operators' => $this->getDeliveryAddressOperators(),
            ],
            [
                'id' => 'categories',
                'label' => lang('igniterlabs.reports::default.label_categories'),
                'type' => 'string',
                'input' => 'select',
                'multiple' => true,
                'values' => Category::getDropdownOptions(),
                'operators' => $this->getCategoriesOperators(),
            ],
            [
                'id' => 'menus',
                'label' => lang('igniterlabs.reports::default.label_menus'),
                'type' => 'string',
                'input' => 'select',
                'values' => Menu::getDropdownOptions(),
                'operators' => $this->getMenusOperators(),
            ],
        ];
    }

    public function defineColumns(): array
    {
        return [
            'customer_name' => [
                'title' => lang('igniterlabs.reports::default.label_customer_name')
            ],
            'email' => [
                'title' => lang('igniterlabs.reports::default.label_email')
            ],
            'order_total' => [
                'title' => lang('igniterlabs.reports::default.label_order_total')
            ],
            'order_date' => [
                'title' => lang('igniterlabs.reports::default.label_order_date'),
                'type' => 'date',
            ],
            'order_type' => [
                'title' => lang('igniterlabs.reports::default.label_order_type')
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        return Order::query()->whereBetween('order_date', [$start, $end]);
    }

    public function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(function (Order $report) {
            return [
                'customer_name' => $report->customer_name,
                'email' => $report->email,
                'order_total' => $report->order_total,
                'order_date' => $report->order_datetime->isoFormat(lang('system::lang.moment.date_time_format')),
                'order_type' => $report->order_type_name,
            ];
        });
    }

    protected function getLocationIdOperators(): array
    {
        return [
            'in', 'not_in',
            'is_empty', 'is_not_empty',
        ];
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

    protected function getCustomerGroupOperators(): array
    {
        return [
            'equal', 'not_equal',
            'in', 'not_in',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }

    protected function getDateAddedOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
        ];
    }

    protected function getOrderDateRelativeOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
        ];
    }

    protected function getOrderDateOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
        ];
    }

    protected function getOrderTypeOperators(): array
    {
        return [
            'in', 'not_in',
        ];
    }

    protected function getDeliveryAddressOperators(): array
    {
        return [
            'equal', 'not_equal',
            'begins_with', 'not_begins_with',
            'contains', 'not_contains',
            'ends_with', 'not_ends_with',
        ];
    }

    protected function getCategoriesOperators(): array
    {
        return [
            'in', 'not_in',
            'is_empty', 'is_not_empty',
        ];
    }

    protected function getMenusOperators(): array
    {
        return [
            'equal', 'not_equal',
            'in', 'not_in',
        ];
    }

    protected function getLocationIdOptions(): array
    {
        return AdminAuth::isSuperUser() ? LocationModel::getDropdownOptions()->all() : Location::currentOrAssigned();
    }
}
