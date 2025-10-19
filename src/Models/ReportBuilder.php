<?php

namespace IgniterLabs\Reports\Models;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\FlashException;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\Classes\Manager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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

    public function getTableData($start, $end, $pageLimit = 5, $currentPage = null): LengthAwarePaginator
    {
        throw_if(!class_exists($this->rule_class),
            FlashException::error(sprintf(lang('igniterlabs.reports::default.alert_report_rule_class_not_found'), $this->rule_class))
        );

        /** @var BaseRule $ruleObject */
        $ruleObject = $this->getRuleObject();

        $reportQuery = $ruleObject->getReportQuery(Carbon::parse($start), Carbon::parse($end));

        if ($ruleRawData = $this->getRawOriginal('rule_data', [])) {
            $reportQuery = (new QueryBuilderParser())->parse($ruleRawData, $reportQuery);
        }

        $tableData = $reportQuery->paginate($pageLimit, ['*'], 'page', $currentPage);

        return $ruleObject->mapTableData($tableData);
    }

    public function getSelectedColumns(): array
    {
        throw_if(!class_exists($this->rule_class),
            FlashException::error(sprintf(lang('igniterlabs.reports::default.alert_report_rule_class_not_found'), $this->rule_class))
        );

        /** @var BaseRule $ruleObject */
        $ruleObject = $this->getRuleObject();

        return collect($ruleObject->defineColumns())
            ->when($this->columns, function ($columns) {
                return $columns->filter(fn($column, $key) => in_array($key, $this->columns));
            })
            ->toArray();
    }

    /**
     * Extends this model with the rule class
     * @param string $class Class name
     */
    public function applyRuleClass($class = null): bool
    {
        if (!$class) {
            $class = $this->rule_class;
        }

        if ($class && !$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->rule_class = $class;

        return true;
    }

    /**
     * @return null|BaseRule
     */
    public function getRuleObject(): mixed
    {
        $this->applyRuleClass();

        return $this->asExtension($this->rule_class);
    }
}
