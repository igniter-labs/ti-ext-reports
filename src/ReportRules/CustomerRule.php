<?php

namespace IgniterLabs\Reports\ReportRules;

use Igniter\Flame\Database\Builder;
use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Support\Carbon;

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
        return [];
    }

    public function defineColumns(): array
    {
        // TODO: Implement defineColumns() method.
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder
    {
        // TODO: Implement fetchReport() method.
    }
}
