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

    protected ?BaseRule $reportRule = null;

    protected ?ReportBuilder $reportBuilder = null;

    protected ?BaseFormWidget $smartWidget;

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
        $this->smartWidget = $this->reportRule ? $this->makeDataTableWidget() : null;
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

    protected function prepareVars()
    {
        $this->vars['widget'] = $this->smartWidget;
        $this->vars['widgetTitle'] = $this->reportBuilder?->name;
    }

    public function loadAssets()
    {
        $this->addCss('igniterlabs.reports::/css/smartreports.css', 'smartreports-css');
        $this->addJs('igniterlabs.reports::/js/smartreports.js', 'smartreports-js');
        $this->addCss('widgets/table.css', 'table-css');
        $this->addJs('widgets/table.js', 'table-js');
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
        $dataTableWidget = $this->makeFormWidget(DataTable::class, [
            'name' => 'reportTable' . $this->reportBuilder->code,
            'label' => lang('igniterlabs.reports::default.label_report_table'),
        ], [
            'model' => new ReportBuilder,
            'columns' => $this->reportRule->defineColumns(),
            'useAjax' => true,
            'alias' => $this->alias . 'ReportTable',
        ]);

        $dataTableWidget->getTable()->unbindEvent(['table.getRecords', 'table.getDropdownOptions']);
        $dataTableWidget->getTable()->bindEvent('table.getRecords', function (int $offset, int $limit, string $search) {
            return $this->reportBuilder->getTableData(
                $this->getStartDate(), $this->getEndDate(), $limit, ($offset / $limit) + 1
            );
        });

        $dataTableWidget->bindToController();

        return $dataTableWidget;
    }
}
