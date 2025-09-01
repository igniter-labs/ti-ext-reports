<?php

namespace IgniterLabs\Reports\DashboardWidgets;

use Igniter\Admin\Classes\BaseDashboardWidget;
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

    protected ?BaseRule $reportRule = null;

    public function __construct($controller, $properties = [])
    {
        foreach ($properties as $property => $value) {
            $properties[$property] = is_array($value) ? json_encode($value) : $value;
        }

        parent::__construct($controller, $properties);
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
        ];
    }

    public function loadAssets()
    {
        $this->addCss('igniterlabs.reports::/css/smartreports.css', 'smartreports-css');
        $this->addJs('igniterlabs.reports::/js/smartreports.js', 'smartreports-js');
    }

    protected function prepareVars()
    {
        $this->loadReportRule();

        $this->vars['dataTableWidget'] = $this->makeDataTableWidget();
    }

    public function onFetchReport()
    {
        $this->loadReportRule();

        $start = $this->getStartDate();
        $end = $this->getEndDate();

        return [
            'data' => $this->reportRule->fetchReport($start, $end),
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

    protected function loadReportRule(): ?BaseRule
    {
        if ($this->reportRule) {
            return $this->reportRule;
        }

        if (!$reportBuilderId = $this->property('report')) {
            return null;
        }

        if (!$reportBuilder = ReportBuilder::find($reportBuilderId)) {
            return null;
        }

        return $this->reportRule = resolve(Manager::class)->getRule($reportBuilder->rule_class);
    }

    protected function makeDataTableWidget()
    {
        if (!$this->reportRule) {
            return null;
        }

        $dataTableWidget = $this->makeFormWidget(DataTable::class, [
            'name' => 'reportTable',
            'label' => lang('igniterlabs.reports::default.label_report_table'),
        ], [
            'model' => new ReportBuilder,
            'columns' => $this->reportRule->defineColumns(),
        ]);

        $dataTableWidget->getTable()->unbindEvent(['table.getRecords', 'table.getDropdownOptions']);
        $dataTableWidget->getTable()->bindEvent('table.getRecords', function(int $offset, int $limit, string $search) {
            $page = ($offset / $limit) + 1;

            $query = $this->reportRule->fetchReport(
                $this->getStartDate(),
                $this->getEndDate(),
            );

            return $query->paginate($limit, ['*'], 'page', $page);
        });

        return $dataTableWidget;
    }
}
