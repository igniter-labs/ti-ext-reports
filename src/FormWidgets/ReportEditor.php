<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\FormWidgets;

use Igniter\Admin\Classes\BaseFormWidget;
use Igniter\Admin\FormWidgets\DatePicker;
use Igniter\Admin\Traits\FormModelWidget;
use Igniter\Admin\Traits\ValidatesForm;
use IgniterLabs\Reports\Classes\Manager;
use Override;

/**
 * @protected $model ReportBuilder
 */
class ReportEditor extends BaseFormWidget
{
    use FormModelWidget;
    use ValidatesForm;

    public array $customFilterInputs = [];

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('reporteditor/reporteditor');
    }

    public function loadAssets(): void
    {
        $this->addCss('css/vendor/query-builder.default.min.css', 'query-builder-css');
        $this->addCss('css/reporteditor.css', 'reporteditor-css');
        $this->addJs('js/vendor/query-builder.standalone.min.js', 'query-builder-js');
        $this->addJs('js/reporteditor.js', 'reporteditor-js');
    }

    public function prepareVars(): void
    {
        $this->vars['field'] = $this->formField;
        $this->vars['rules'] = $this->getLoadValue();
        $this->vars['filters'] = $this->makeFilters();
    }

    protected function makeFilters(): array
    {
        $reportRule = resolve(Manager::class)->getRule($this->model->rule_class);

        return collect($reportRule->defineFilters() ?? [])->each(function(array $filter): void {
            match ($filter['input']) {
                'datepicker' => $this->renderDatePickerInput($filter),
                default => null,
            };
        })->all();
    }

    protected function renderDatePickerInput(array $filter): void
    {
        $this->customFilterInputs[$filter['id']] = $this->makeFormWidget(DatePicker::class, [
            'name' => $filter['id'],
            'label' => $filter['label'],
            'type' => 'datepicker',
        ], [
            'mode' => $filter['type'] ?? 'date',
            'dateFormat' => $filter['dateFormat'] ?? null,
            'startDate' => $filter['startDate'] ?? null,
            'endDate' => $filter['endDate'] ?? null,
            'model' => $this->model,
        ])->render();
    }

    #[Override]
    public function getSaveValue($value): mixed
    {
        return json_decode($value ?? '', true);
    }
}
