<?php

namespace IgniterLabs\Reports\Actions;

use Igniter\Admin\DashboardWidgets\Statistics;

class DashboardWidgets
{
    public function defineWidgets()
    {
        $this->defineCards();
        $this->defineCharts();
    }

    protected function defineCards()
    {
        Statistics::registerCards(fn(): array => [

        ]);
    }

    protected function defineCharts() {}

    public function getValue($code, $start, $end, callable $callback): string|int
    {
        return match ($code) {
            'sale' => $this->getTotalSaleSum($callback),
            'lost_sale' => $this->getTotalLostSaleSum($callback),
            'cash_payment' => $this->getTotalCashPaymentSum($callback),
            'order' => $this->getTotalOrderSum($callback),
            'delivery_order' => $this->getTotalDeliveryOrderSum($callback),
            'collection_order' => $this->getTotalCollectionOrderSum($callback),
            'completed_order' => $this->getTotalCompletedOrderSum($callback),
            default => 0,
        };
    }

}
