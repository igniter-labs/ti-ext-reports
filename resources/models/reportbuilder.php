<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
                'create' => [
                    'label' => 'lang:igniter::admin.button_new',
                    'class' => 'btn btn-primary',
                    'href' => 'report_builder/create',
                ],
            ],
        ],
        'bulkActions' => [
            'delete' => [
                'label' => 'lang:igniter::admin.button_delete',
                'class' => 'btn btn-light text-danger',
                'data-request-confirm' => 'lang:igniter::admin.alert_warning_confirm',
                'permissions' => 'IgniterLabs.Reports.Manage',
            ],
        ],
        'filter' => [],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'report_builder/edit/{id}',
                ],
            ],
            'name' => [
                'label' => 'lang:igniterlabs.reports::default.column_name',
                'type' => 'text',
                'sortable' => true,
            ],
            'code' => [
                'label' => 'lang:igniterlabs.reports::default.column_code',
                'type' => 'text',
                'sortable' => true,
            ],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => [
                    'label' => 'lang:igniter::admin.button_save',
                    'context' => ['create', 'edit'],
                    'partial' => 'form/toolbar_save_button',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                    'data-progress-indicator' => 'igniter::admin.text_saving',
                ],
                'delete' => [
                    'label' => 'lang:igniter::admin.button_icon_delete',
                    'class' => 'btn btn-danger',
                    'data-request' => 'onDelete',
                    'data-request-data' => "_method:'DELETE'",
                    'data-request-confirm' => 'lang:igniter::admin.alert_warning_confirm',
                    'data-progress-indicator' => 'igniter::admin.text_deleting',
                    'context' => ['edit'],
                ],
            ],
        ],
        'fields' => [
            'rule_class' => [
                'label' => 'lang:igniterlabs.reports::default.label_rule_class',
                'type' => 'select',
                'comment' => 'lang:igniterlabs.reports::default.help_rule_class',
            ],
            'name' => [
                'label' => 'lang:igniterlabs.reports::default.label_name',
                'type' => 'text',
                'span' => 'left',
            ],
            'code' => [
                'label' => 'lang:igniterlabs.reports::default.label_code',
                'type' => 'text',
                'span' => 'right',
            ],
            'description' => [
                'label' => 'lang:igniterlabs.reports::default.label_description',
                'type' => 'textarea',
            ],
            'rule_data' => [
                'label' => 'lang:igniterlabs.reports::default.label_rules',
                'type' => 'reporteditor',
                'commentAbove' => 'lang:igniterlabs.reports::default.help_rules',
                'context' => 'edit',
            ],
            'columns' => [
                'label' => 'lang:igniterlabs.reports::default.label_columns',
                'type' => 'checkboxlist',
                'context' => 'edit',
                'commentAbove' => 'lang:igniterlabs.reports::default.help_columns',
                'options' => [],
            ],
        ],
    ],
];
