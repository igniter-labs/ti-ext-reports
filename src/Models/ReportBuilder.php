<?php

namespace IgniterLabs\Reports\Models;

use Igniter\Flame\Database\Model;
use IgniterLabs\Reports\Classes\BaseRule;
use IgniterLabs\Reports\Classes\Manager;

class ReportBuilder extends Model
{
    protected $table = 'reports_builder';

    public $timestamps = true;

    public $casts = [
        'rule_data' => 'array',
        'columns' => 'array',
    ];

    protected $guarded = [];

    public static function getDropdownOptions()
    {
        return static::query()->dropdown('name');
    }

    public function getRuleClassOptions()
    {
        return collect(resolve(Manager::class)->loadRules())->mapWithKeys(function(BaseRule $ruleObject, $className) {
            return [$className => array_get($ruleObject->ruleDetails(), 'name')];
        });
    }
}
