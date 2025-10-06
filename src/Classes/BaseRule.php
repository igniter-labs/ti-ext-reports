<?php

namespace IgniterLabs\Reports\Classes;

use Igniter\Local\Traits\LocationAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class BaseRule
{
    use LocationAwareWidget;
    abstract public function ruleDetails(): array;

    abstract public function defineFilters(): array;

    abstract public function defineColumns(): array;

    abstract public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder;

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

    public static function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery;
    }

    public function getChartDataset(Carbon $start, Carbon $end): array
    {
        return [];
    }

    protected function generateBackgroundColor(string $string): string
    {
        return sprintf('hsl(%s, 70%%, 60%%)', crc32('background-color-' . $string) % 360);
    }
}
