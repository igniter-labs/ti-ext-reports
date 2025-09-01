<?php

namespace IgniterLabs\Reports\Classes;

use Igniter\Flame\Database\Builder;
use Illuminate\Support\Carbon;

abstract class BaseRule
{
    abstract public function ruleDetails(): array;

    abstract public function defineFilters(): array;

    abstract public function defineColumns(): array;

    abstract public function getReportQuery(Carbon $start, Carbon $end): Builder;
}
