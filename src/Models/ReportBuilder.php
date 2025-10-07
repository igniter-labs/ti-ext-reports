<?php

namespace IgniterLabs\Reports\Models;

use Carbon\Carbon;
use Igniter\Flame\Database\Model;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\Classes\Manager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use timgws\QueryBuilderParser;

class ReportBuilder extends Model
{
    protected $table = 'reports_builder';

    public $timestamps = true;

    public $casts = [
        'rule_data' => 'array',
        'columns' => 'array',
    ];

    protected $guarded = [];

    public static function getDropdownOptions()
    {
        return static::query()->dropdown('name');
    }

    public function getRuleClassOptions()
    {
        return collect(resolve(Manager::class)->loadRules())->mapWithKeys(function(BaseRule $ruleObject, $className) {
            return [$className => array_get($ruleObject->ruleDetails(), 'name')];
        });
    }

    public function getQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        $ruleClass = $this->rule_class;
        $this->validateRuleClass($ruleClass);

        $ruleObject = resolve($ruleClass);

        $reportQuery = $ruleObject->getReportQuery($start, $end);
        if ($this->rule_data && is_array($this->rule_data)) {
            return (new QueryBuilderParser())->parse($this->getRawOriginal('rule_data'), $reportQuery);
        }

        return $reportQuery;
    }

    public function getTableData($start, $end, $pageLimit = 5, $currentPage = null)
    {
        $ruleClass = $this->rule_class;
        $this->validateRuleClass($ruleClass);

        return $this->rule_class::mapTableData(
            $this->getQuery($start, $end)->paginate($pageLimit, ['*'], 'page', $currentPage)
        );
    }

    public function validateRuleClass(string $className)
    {
        if (!class_exists($className))
            throw new \Exception(sprintf(lang('igniterlabs.reports::default.alert_report_rule_class_not_found'), $className));
    }

    public function getSelectedColumns(): array
    {
        $ruleClass = $this->rule_class;
        $this->validateRuleClass($ruleClass);

        $definedColumns = collect(resolve($ruleClass)->defineColumns());

        if (!$this->columns) {
            return $definedColumns->toArray();
        }

        return $definedColumns->filter(fn($column, $key) => in_array($key, $this->columns))->toArray();
    }
}
