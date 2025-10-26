<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Http\Requests;

use Igniter\System\Classes\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class ReportBuilderRequest extends FormRequest
{
    #[Override]
    public function attributes(): array
    {
        return [
            'name' => lang('lang:igniterlabs.reports::default.label_name'),
            'code' => lang('lang:igniterlabs.reports::default.label_code'),
            'description' => lang('lang:igniterlabs.reports::default.label_description'),
            'rule_class' => lang('lang:igniterlabs.reports::default.label_rule_class'),
            'rule_data' => lang('lang:igniterlabs.reports::default.label_rules'),
            'columns' => lang('lang:igniterlabs.reports::default.label_columns'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'alpha_dash', 'max:255', Rule::unique('reports_builder', 'code')->ignore($this->getRecordId())],
            'description' => ['nullable', 'string', 'max:500'],
            'rule_class' => ['sometimes', 'required', 'string'],
            'rule_data' => ['sometimes', 'nullable', 'array'],
            'columns' => ['sometimes', 'nullable', 'array'],
            'columns.*' => ['required', 'string'],
        ];
    }
}
