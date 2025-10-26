<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Models;

use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\FlashException;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\Classes\Manager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use timgws\QueryBuilderParser;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property null|string $description
 * @property null|string $rule_class
 * @property null|array $rule_data
 * @property null|array $columns
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ReportBuilder extends Model
{
    use HasFactory;

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
        return collect(resolve(Manager::class)->loadRules())->mapWithKeys(fn(BaseRule $ruleObject, $className): array => [$className => array_get($ruleObject->ruleDetails(), 'name')]);
    }

    public function getTableData(DateTimeInterface|WeekDay|Month|string|int|float|null $start, DateTimeInterface|WeekDay|Month|string|int|float|null $end, $pageLimit = 5, $currentPage = null): LengthAwarePaginator
    {
        throw_if(!class_exists($this->rule_class),
            FlashException::error(sprintf(lang('igniterlabs.reports::default.alert_report_rule_class_not_found'), $this->rule_class))
        );

        /** @var BaseRule $ruleObject */
        $ruleObject = $this->getRuleObject();

        $reportQuery = $ruleObject->getReportQuery(Carbon::parse($start), Carbon::parse($end));

        if ($ruleRawData = $this->getRawOriginal('rule_data', [])) {
            $reportQuery = (new QueryBuilderParser)->parse($ruleRawData, $reportQuery);
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
            ->when($this->columns, fn($columns) => $columns->filter(fn($column, $key): bool => in_array($key, $this->columns)))
            ->toArray();
    }

    public function getRuleObject(): ?BaseRule
    {
        return resolve($this->rule_class);
    }
}
