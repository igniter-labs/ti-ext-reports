<?php

namespace IgniterLabs\Reports\DashboardWidgets;

use Igniter\Admin\Classes\BaseDashboardWidget;
use Igniter\Admin\Classes\BaseFormWidget;
use Igniter\Admin\FormWidgets\DataTable;
use Igniter\Local\Traits\LocationAwareWidget;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\Models\ReportBuilder;

class SmartReports extends BaseDashboardWidget
{
    use LocationAwareWidget;

    /**
     * @var string A unique alias to identify this widget.
     */
    protected string $defaultAlias = 'smartreports';

    protected ?BaseRule $reportRule;

    protected ?ReportBuilder $reportBuilder;

    protected ?BaseFormWidget $dataTableWidget;

    public function __construct($controller, $properties = [])
    {
        foreach ($properties as $property => $value) {
            $properties[$property] = is_array($value) ? json_encode($value) : $value;
        }

        parent::__construct($controller, $properties);
    }

    public function initialize(): void
    {
        $this->loadReport();
        $this->dataTableWidget = $this->makeDataTableWidget();
    }

    /**
     * Renders the widget.
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('smartreports/smartreports');
    }

    public function defineProperties(): array
    {
        return [
            'report' => [
                'label' => 'lang:igniterlabs.reports::default.label_report',
                'type' => 'select',
                'comment' => 'lang:igniterlabs.reports::default.help_report',
                'options' => ReportBuilder::getDropdownOptions(...),
                'validationRule' => 'required|string',
            ],
            'type' => [
                'label' => 'lang:igniterlabs.reports::default.label_type',
                'type' => 'select',
                'options' => [
                    'table' => 'lang:igniterlabs.reports::default.text_type_table',
                    'chart' => 'lang:igniterlabs.reports::default.text_type_chart',
                ],
                'validationRule' => 'required|string|in:table',
            ],
            'chart_type' => [
                'label' => 'lang:igniterlabs.reports::default.label_chart_type',
                'type' => 'select',
                'options' => [
                    'line' => 'lang:igniterlabs.reports::default.text_chart_line',
                    'bar' => 'lang:igniterlabs.reports::default.text_chart_bar',
                    'pie' => 'lang:igniterlabs.reports::default.text_chart_pie',
                ],
                'validationRule' => 'nullable|string|in:line,bar,pie,doughnut',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'type',
                    'condition' => 'value[chart]',
                ]
            ],
        ];
    }

    public function loadAssets()
    {
        $this->addCss('igniterlabs.reports::/css/smartreports.css', 'smartreports-css');
        $this->addJs('igniterlabs.reports::/js/smartreports.js', 'smartreports-js');
        $this->addCss('widgets/table.css', 'table-css');
        $this->addJs('widgets/table.js', 'table-js');
    }

    protected function prepareVars()
    {
        $this->vars['dataTableWidget'] = $this->dataTableWidget;
    }

    public function onFetchReport()
    {
        $this->loadReport();

        $start = $this->getStartDate();
        $end = $this->getEndDate();

        return [
            'data' => $this->reportRule->getReportQuery($start, $end),
        ];
    }

    public function getStartDate(): mixed
    {
        if (method_exists(get_parent_class($this), 'getStartDate')) {
            return parent::getStartDate();
        }

        return $this->property('startDate');
    }

    public function getEndDate(): mixed
    {
        if (method_exists(get_parent_class($this), 'getEndDate')) {
            return parent::getEndDate();
        }

        return $this->property('endDate');
    }

    protected function loadReport(): void
    {
        if ($reportBuilderId = $this->property('report')) {
            $this->reportBuilder = ReportBuilder::find($reportBuilderId);
        }

        if ($this->reportBuilder) {
            $this->reportRule = resolve(Manager::class)->getRule($this->reportBuilder->rule_class);
        }
    }

    protected function makeDataTableWidget(): ?BaseFormWidget
    {
        if (!$this->reportRule) {
            return null;
        }

        $dataTableWidget = $this->makeFormWidget(DataTable::class, [
            'name' => 'reportTable',
            'label' => lang('igniterlabs.reports::default.label_report_table'),
        ], [
            'model' => new ReportBuilder,
            'columns' => $this->reportRule->getSelectedColumns($this->reportBuilder->columns),
            'useAjax' => true,
            'alias' => $this->alias . 'ReportTable',
            'pageLimit' => 5,
        ]);

        $dataTableWidget->getTable()->unbindEvent(['table.getRecords', 'table.getDropdownOptions']);
        $dataTableWidget->getTable()->bindEvent('table.getRecords', function (int $offset, int $limit, string $search) {
            $page = ($offset / $limit) + 1;
            $query = $this->reportRule->getReportQuery(
                $this->getStartDate(),
                $this->getEndDate(),
            );

            return $query->paginate($limit, ['*'], 'page', $page);
        });

        return $dataTableWidget;
    }
}
