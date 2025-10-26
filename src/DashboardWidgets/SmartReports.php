<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\DashboardWidgets;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Classes\BaseDashboardWidget;
use Igniter\Admin\Classes\BaseFormWidget;
use Igniter\Admin\FormWidgets\DataTable;
use Igniter\Local\Traits\LocationAwareWidget;
use IgniterLabs\Reports\Models\ReportBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

class SmartReports extends BaseDashboardWidget
{
    use LocationAwareWidget;

    /**
     * @var string A unique alias to identify this widget.
     */
    protected string $defaultAlias = 'smartreports';

    protected ?ReportBuilder $reportBuilder = null;

    protected ?BaseFormWidget $smartWidget = null;

    public function __construct(AdminController $controller, array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $properties[$property] = is_array($value) ? json_encode($value) : $value;
        }

        parent::__construct($controller, $properties);
    }

    public function initialize(): void
    {
        $this->loadReport();
        $this->smartWidget = $this->reportBuilder instanceof ReportBuilder ? $this->makeDataTableWidget() : null;
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

    public function prepareVars(): void
    {
        $this->vars['widget'] = $this->smartWidget;
        $this->vars['widgetTitle'] = $this->reportBuilder?->name;
    }

    public function loadAssets(): void
    {
        $this->addCss('css/smartreports.css', 'smartreports-css');
        $this->addJs('js/smartreports.js', 'smartreports-js');
        $this->addCss('widgets/table.css', 'table-css');
        $this->addJs('widgets/table.js', 'table-js');
    }

    protected function loadReport(): void
    {
        if ($reportBuilderId = $this->property('report')) {
            $this->reportBuilder = ReportBuilder::query()->find($reportBuilderId);
        }
    }

    protected function makeDataTableWidget(): ?BaseFormWidget
    {
        /** @var DataTable $dataTableWidget */
        $dataTableWidget = $this->makeFormWidget(DataTable::class, [
            'name' => 'reportTable'.$this->reportBuilder->code,
            'label' => lang('igniterlabs.reports::default.label_report_table'),
        ], [
            'model' => new ReportBuilder,
            'columns' => $this->reportBuilder->getSelectedColumns(),
            'useAjax' => true,
            'alias' => $this->alias.'ReportTable',
        ]);

        $dataTableWidget->getTable()->unbindEvent(['table.getRecords', 'table.getDropdownOptions']);
        $dataTableWidget->getTable()->bindEvent('table.getRecords', fn(int $offset, int $limit): LengthAwarePaginator => $this->reportBuilder->getTableData(
            $this->getStartDate(), $this->getEndDate(), $limit, ($offset / $limit) + 1
        ));

        $dataTableWidget->bindToController();

        return $dataTableWidget;
    }
}
