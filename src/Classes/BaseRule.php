<?php

namespace IgniterLabs\Reports\Classes;

use Igniter\Local\Traits\LocationAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRule
{
    use LocationAwareWidget;
    abstract public function ruleDetails(): array;

    abstract public function defineFilters(): array;

    abstract public function defineColumns(): array;

    abstract public function getReportQuery(Carbon $start, Carbon $end): Builder;

    public function getSelectedColumns(array $selectedColumns): array
    {
        return collect($this->defineColumns())
            ->filter(function ($column, $key) use ($selectedColumns) {
                return in_array($key, $selectedColumns);
            })->toArray();
    }

    protected function getTextOperators(): array
    {
        return [
            'equal', 'not_equal',
            'begins_with', 'not_begins_with',
            'contains', 'not_contains',
            'ends_with', 'not_ends_with',
            'is_empty', 'is_not_empty',
            'is_null', 'is_not_null'
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

    public function getTableData(Carbon $start, Carbon $end, int $pageLimit = 5, $currentPage = null): LengthAwarePaginator
    {
        return $this->getReportQuery($start, $end)->paginate($pageLimit, ['*'], 'page', $currentPage);
    }
}
