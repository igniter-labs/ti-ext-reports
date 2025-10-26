<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\Classes;

use Igniter\Flame\Database\Builder;
use Igniter\Flame\Database\Query\Builder as QueryBuilder;
use Igniter\Local\Traits\LocationAwareWidget;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\ReportRules\OrderRule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

it('maps table data by default', function(): void {
    $rule = new class extends BaseRule
    {
        public function ruleDetails(): array
        {
            // TODO: Implement ruleDetails() method.
        }

        public function defineFilters(): array
        {
            // TODO: Implement defineFilters() method.
        }

        public function defineColumns(): array
        {
            // TODO: Implement defineColumns() method.
        }

        public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
        {
            // TODO: Implement getReportQuery() method.
        }
    };
    $paginatedData = new LengthAwarePaginator([], 0, 10);

    $result = $rule->mapTableData($paginatedData);

    expect($result)->toBe($paginatedData);
});

it('has extension & location aware widget trait', function(): void {
    $rule = new OrderRule;

    expect(class_uses_recursive($rule))->toHaveKey(LocationAwareWidget::class);
});
