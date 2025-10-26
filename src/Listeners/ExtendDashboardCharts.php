<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Listeners;

use Igniter\Admin\DashboardWidgets\Charts;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Order;
use Igniter\Cart\Models\OrderMenu;
use Igniter\Local\Traits\LocationAwareWidget;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\DB;

class ExtendDashboardCharts
{
    use LocationAwareWidget;

    public function registerCharts(): void
    {
        Charts::extend(function(Charts $charts): void {
            $charts->bindEvent('charts.extendDatasets', function() use ($charts): void {
                $charts->addDataset('top_customers', [
                    'label' => 'igniterlabs.reports::default.text_top_customers',
                    'type' => 'doughnut',
                    'icon' => 'fa fa-users',
                    'datasetFrom' => $this->getDatasetConfig(...),
                ]);
                $charts->addDataset('bottom_customers', [
                    'label' => 'igniterlabs.reports::default.text_bottom_customers',
                    'type' => 'doughnut',
                    'icon' => 'fa fa-users',
                    'datasetFrom' => $this->getDatasetConfig(...),
                ]);
                $charts->addDataset('best_selling_items', [
                    'label' => 'igniterlabs.reports::default.text_best_selling_items',
                    'type' => 'doughnut',
                    'icon' => 'fa fa-box-open',
                    'datasetFrom' => $this->getDatasetConfig(...),
                ]);
                $charts->addDataset('worst_selling_items', [
                    'label' => 'igniterlabs.reports::default.text_worst_selling_items',
                    'type' => 'doughnut',
                    'icon' => 'fa fa-box-open',
                    'datasetFrom' => $this->getDatasetConfig(...),
                ]);
            });
        });
    }

    public function getDatasetConfig($activeDataset, $start, $end): array
    {
        return match ($activeDataset) {
            'top_customers' => $this->getTopCustomersDataset($start, $end),
            'bottom_customers' => $this->getBottomCustomersDataset($start, $end),
            'best_selling_items' => $this->getBestSellingItemsDataset($start, $end),
            'worst_selling_items' => $this->getWorstSellingItemsDataset($start, $end),
            default => [],
        };
    }

    protected function getTopCustomersDataset($start, $end): array
    {
        return $this->getCustomersDataset($start, $end, function($query): void {
            $query->orderByDesc('count');
        });
    }

    protected function getBottomCustomersDataset($start, $end): array
    {
        return $this->getCustomersDataset($start, $end, function($query): void {
            $query->orderBy('count');
        });
    }

    protected function getBestSellingItemsDataset($start, $end): array
    {
        return $this->getItemsDataset($start, $end, function($query): void {
            $query->orderByDesc('count');
        });
    }

    protected function getWorstSellingItemsDataset($start, $end): array
    {
        return $this->getItemsDataset($start, $end, function($query): void {
            $query->orderBy('count');
        });
    }

    protected function getCustomersDataset($start, $end, $callback): array
    {
        $customerTable = DB::getTablePrefix().(new Customer)->getTable();
        $orderTable = DB::getTablePrefix().(new Order)->getTable();
        $query = Customer::query()
            ->whereBetween('orders.created_at', [$start, $end])
            ->select([
                DB::raw(sprintf('SUM(%s.order_total) as count', $orderTable)),
                DB::raw(sprintf("CONCAT(%s.first_name, ' ', %s.last_name) as label", $customerTable, $customerTable)),
            ])
            ->join('orders', 'orders.customer_id', '=', 'customers.customer_id')
            ->where('orders.status_id', setting('completed_order_status'));

        $this->locationApplyScope($query);
        $callback($query);

        $result = $query->groupBy('customers.customer_id')->limit(10)->get();

        return [
            'labels' => $result->map(fn($item) => ($item->label))->all(),
            'datasets' => [
                [
                    'backgroundColor' => $result->map(fn($item): string => $this->generateBackgroundColor(
                        (string)$item->label))->all(),
                    'data' => $result->map(fn($item): int => (int)$item->count)->all(),
                ],
            ],
        ];
    }

    protected function getItemsDataset($start, $end, $callback): array
    {
        $menuTable = DB::getTablePrefix().(new Menu)->getTable();
        $orderMenuTable = DB::getTablePrefix().(new OrderMenu)->getTable();

        $query = Menu::query()
            ->whereBetween('orders.created_at', [$start, $end])
            ->select([
                DB::raw(sprintf('SUM(%s.quantity) as count', $orderMenuTable)),
                DB::raw(sprintf('%s.menu_name as label', $menuTable)),
            ])
            ->join('order_menus', 'order_menus.menu_id', '=', 'menus.menu_id')
            ->join('orders', 'orders.order_id', '=', 'order_menus.order_id')
            ->whereIn('orders.status_id', setting('completed_order_status'));

        $this->locationApplyScope($query);
        $callback($query);

        $result = $query->groupBy('menus.menu_id')->limit(10)->get();

        return [
            'labels' => $result->map(fn($item) => ($item->label))->all(),
            'datasets' => [
                [
                    'backgroundColor' => $result->map(fn($item): string => $this->generateBackgroundColor(
                        (string)$item->label))->all(),
                    'data' => $result->map(fn($item): int => (int)$item->count)->all(),
                ],
            ],
        ];
    }

    protected function generateBackgroundColor(string $string): string
    {
        return sprintf('hsl(%s, 70%%, 60%%)', crc32('background-color-'.$string) % 360);
    }
}
