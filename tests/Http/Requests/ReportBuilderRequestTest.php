<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Tests\Http\Requests;

use IgniterLabs\Reports\Http\Requests\ReportBuilderRequest;

it('returns correct attribute labels', function(): void {
    $request = new ReportBuilderRequest;

    $attributes = $request->attributes();

    expect($attributes)->toHaveKey('name', lang('igniterlabs.reports::default.label_name'))
        ->and($attributes)->toHaveKey('code', lang('igniterlabs.reports::default.label_code'))
        ->and($attributes)->toHaveKey('description', lang('igniterlabs.reports::default.label_description'))
        ->and($attributes)->toHaveKey('rule_class', lang('igniterlabs.reports::default.label_rule_class'))
        ->and($attributes)->toHaveKey('rule_data', lang('igniterlabs.reports::default.label_rules'))
        ->and($attributes)->toHaveKey('columns', lang('igniterlabs.reports::default.label_columns'));
});

it('returns correct validation rules', function(): void {
    $request = new ReportBuilderRequest;

    $rules = $request->rules();

    expect($rules)->toHaveKey('name')
        ->and($rules)->toHaveKey('code')
        ->and($rules)->toHaveKey('description')
        ->and($rules)->toHaveKey('rule_class')
        ->and($rules)->toHaveKey('rule_data')
        ->and($rules)->toHaveKey('columns')
        ->and($rules)->toHaveKey('columns.*')
        ->and($rules['name'])->toContain('required', 'string', 'max:255')
        ->and($rules['code'])->toContain('required', 'alpha_dash', 'max:255')
        ->and($rules['description'])->toContain('nullable', 'string', 'max:500')
        ->and($rules['rule_class'])->toContain('sometimes', 'required', 'string')
        ->and($rules['rule_data'])->toContain('sometimes', 'nullable', 'array')
        ->and($rules['columns'])->toContain('sometimes', 'nullable', 'array')
        ->and($rules['columns.*'])->toContain('required', 'string');
});
