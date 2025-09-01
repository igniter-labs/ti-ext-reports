<?php

namespace IgniterLabs\Reports\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;
use Igniter\Admin\Widgets\Form;
use IgniterLabs\Reports\Classes\Manager;
use IgniterLabs\Reports\Http\Requests\ReportBuilderRequest;
use IgniterLabs\Reports\Models\ReportBuilder as ReportBuilderModel;

class ReportBuilder extends AdminController
{
    public array $implement = [
        ListController::class,
        FormController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => ReportBuilderModel::class,
            'title' => 'igniterlabs.reports::default.text_title',
            'emptyMessage' => 'igniterlabs.reports::default.text_empty',
            'defaultSort' => ['title', 'ASC'],
            'configFile' => 'reportbuilder',
        ],
    ];
    public $formConfig = [
        'name' => 'igniterlabs.reports::default.text_form_name',
        'model' => ReportBuilderModel::class,
        'request' => ReportBuilderRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'report_builder/edit/{id}',
            'redirectClose' => 'report_builder',
            'redirectNew' => 'report_builder/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'report_builder/edit/{id}',
            'redirectClose' => 'report_builder',
            'redirectNew' => 'report_builder/create',
        ],
        'delete' => [
            'redirect' => 'report_builder',
        ],
        'configFile' => 'reportbuilder',
    ];

    protected string|array|null $requiredPermissions = 'IgniterLabs.Reports.Manage';

    public static function getSlug(): string
    {
        return 'report_builder';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('reportbuilder', 'tools');
    }

    public function formExtendFieldsBefore(Form $form): void
    {
        if ($form->context === 'create') {
            return;
        }

        $reportRule = resolve(Manager::class)->getRule($form->model->rule_class);
        $form->fields['columns']['options'] = collect($reportRule?->defineColumns() ?? [])->all();
    }

    public function formExtendFields(Form $form): void
    {
        if ($form->context !== 'create') {
            $form->getField('rule_class')->disabled = true;
        }
    }
}
