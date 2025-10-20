<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\System\Facades\Assets;
use IgniterLabs\Reports\FormWidgets\ReportEditor;
use IgniterLabs\Reports\Models\ReportBuilder;

beforeEach(function(): void {
    $this->reportBuilder = ReportBuilder::factory()->create();
    $this->reportEditorWidget = new ReportEditor(
        resolve(\IgniterLabs\Reports\Http\Controllers\ReportBuilder::class),
        new FormField('rule_data', 'Report Editor'),
        [
            'model' => $this->reportBuilder,
        ],
    );
});

it('prepares vars correctly', function(): void {
    $this->reportEditorWidget->prepareVars();

    expect($this->reportEditorWidget->vars['field'])->toBeInstanceOf(FormField::class)
        ->and($this->reportEditorWidget->vars['rules'])->toBeArray()
        ->and($this->reportEditorWidget->vars['filters'])->toBeArray();
});

it('loads assets correctly', function(): void {
    Assets::shouldReceive('addCss')->once()->with('css/vendor/query-builder.default.min.css', 'query-builder-css');
    Assets::shouldReceive('addCss')->once()->with('css/reporteditor.css', 'reporteditor-css');

    Assets::shouldReceive('addJs')->once()->with('js/vendor/query-builder.standalone.min.js', 'query-builder-js');
    Assets::shouldReceive('addJs')->once()->with('js/reporteditor.js', 'reporteditor-js');

    $this->reportEditorWidget->assetPath = [];

    $this->reportEditorWidget->loadAssets();
});

it('renders report editor widget', function(): void {
    expect($this->reportEditorWidget->render())->toBeString();
});

it('gets save value', function(): void {
    expect($this->reportEditorWidget->getSaveValue(json_encode(['rule_data'])))->toBeArray()
        ->and($this->reportEditorWidget->getSaveValue(null))->toBeNull();
});
