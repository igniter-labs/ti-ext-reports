<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Database\Factories;

use IgniterLabs\Reports\Models\ReportBuilder;
use IgniterLabs\Reports\ReportRules\OrderRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportBuilderFactory extends Factory
{
    protected $model = ReportBuilder::class;

    public function definition(): array
    {
        return [
            'code' => 'report_'.$this->faker->unique()->numberBetween(1000, 9999),
            'name' => 'Test Report '.$this->faker->numberBetween(1, 100),
            'description' => 'Test report description',
            'rule_class' => OrderRule::class,
            'rule_data' => [
                'rules' => [
                    //                    [
                    //                        'id' => 'order_type',
                    //                        'type' => 'string',
                    //                        'field' => 'order_type',
                    //                        'input' => 'select',
                    //                        'value' => ['collection', 'delivery'],
                    //                        'operator' => 'in'
                    //                    ]
                ],
                'valid' => true,
                'condition' => 'AND',
            ],
            'columns' => ['order_id', 'order_total', 'order_date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
