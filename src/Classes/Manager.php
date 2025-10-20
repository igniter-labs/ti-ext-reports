<?php

declare(strict_types=1);

namespace IgniterLabs\Reports\Classes;

use Igniter\System\Classes\ExtensionManager;
use Illuminate\Support\Collection;

class Manager
{
    public function getRule($className): BaseRule
    {
        return $this->loadRules()->get($className);
    }

    public function loadRuleFilters(): Collection
    {
        return collect($this->findRules())->mapWithKeys(function($className): array {
            /** @var BaseRule $ruleObject */
            $ruleObject = resolve($className);

            return [$className => collect($ruleObject->defineFilters())];
        });
    }

    public function loadRules(): Collection
    {
        return collect($this->findRules())->mapWithKeys(fn($className): array => [$className => resolve($className)]);
    }

    public function findRules(): array
    {
        $results = [];
        $bundles = resolve(ExtensionManager::class)->getRegistrationMethodValues('registerReportRules');
        foreach ($bundles as $reportRules) {
            foreach ($reportRules as $className) {
                $results[] = $className;
            }
        }

        return $results;
    }
}
