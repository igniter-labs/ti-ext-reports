<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Classes;

use Igniter\Flame\Database\Builder;
use Igniter\Flame\Database\Query\Builder as QueryBuilder;
use Igniter\Local\Traits\LocationAwareWidget;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

abstract class BaseRule
{
    use LocationAwareWidget;

    abstract public function ruleDetails(): array;

    abstract public function defineFilters(): array;

    abstract public function defineColumns(): array;

    abstract public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder;

    public function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery;
    }

    protected function getTextOperators(): array
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

    protected function getNumericOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null',
        ];
    }

    protected function getDateOperators(): array
    {
        return [
            'equal', 'not_equal',
            'less', 'less_or_equal',
            'greater', 'greater_or_equal',
            'between', 'not_between',
        ];
    }
}
