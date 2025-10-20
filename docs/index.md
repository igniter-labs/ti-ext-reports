---
title: "Reports"
section: "extensions"
sortOrder: 999
---

## Installation

You can install the extension via composer using the following command:

```bash
composer require igniterlabs/ti-ext-reports -W
```

Run the database migrations to create the required tables:

```bash
php artisan igniter:up
```

## Getting started

### Creating a report

1. Go to **Tools > Report Builder** in the admin area.
2. Click the **New** button to create a new report.
3. Fill in the report details:
   - **Name**: Enter a descriptive name for your report
   - **Code**: A unique identifier for the report (auto-generated if left empty)
   - **Description**: Optional description of what the report shows
   - **Rule**: Select the type of report rule to apply
4. Configure the report filters and conditions using the visual query builder.
5. Select which columns to display in the report and include in CSV exports.
6. Save the report.

### Displaying smart reports on the dashboard

1. Go to **Dashboard** in the admin area.
2. Click on the **+ Add Widget** button.
3. Select **Smart Reports** from the widget options
4. Choose a width for the widget
5. Select the report you created earlier from the **Report** dropdown.
6. Click **Add** to save.
7. The report data will now be displayed on your dashboard.

### Displaying built-in dashboard charts reports

1. Go to **Dashboard** in the admin area.
2. Click on the **+ Add Widget** button.
3. Select **Charts Widget** from the widget options
4. Choose a width for the widget
5. Select a built-in report type from the **Datasets** dropdown.
   - **Available built-in datasets include:**
       - Best Selling Items
       - Worst Selling Items
       - Top Customers
       - Bottom Customers
6. Click **Add** to save.
7. The chart will now be displayed on your dashboard.

## Usage

This section covers how to integrate the Reports extension into your own extension if you're building an extension that needs to provide custom reporting functionality.

### Creating custom report rules

Report rules define how data is filtered, queried, and displayed. To create a custom report rule, create a class that extends the `IgniterLabs\Reports\Classes\BaseRule` class.

A report rule class is typically stored in the `src/ReportRules` directory of your extension. Here's an example of a custom report rule:

```php
namespace Author\Extension\ReportRules;

use IgniterLabs\Reports\Classes\BaseRule;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

class CustomReportRule extends BaseRule
{
    public function ruleDetails(): array
    {
        return [
            'name' => 'Custom Report',
            'description' => 'A custom report for analyzing specific data',
        ];
    }

    public function defineFilters(): array
    {
        return [
            [
                'id' => 'custom_field',
                'label' => 'Custom Field',
                'type' => 'string',
                'input' => 'text',
                'operators' => ['equal', 'not_equal', 'contains', 'not_contains'],
            ],
            [
                'id' => 'date_range',
                'label' => 'Date Range',
                'type' => 'date',
                'input' => 'datepicker',
                'validation' => ['format' => 'YYYY/MM/DD'],
                'operators' => ['equal', 'not_equal', 'less', 'greater'],
            ],
        ];
    }

    public function defineColumns(): array
    {
        return [
            'custom_field' => [
                'title' => 'Custom Field',
            ],
            'date_field' => [
                'title' => 'Date Field',
                'type' => 'date',
            ],
            'amount_field' => [
                'title' => 'Amount',
            ],
        ];
    }

    public function getReportQuery(Carbon $start, Carbon $end): Builder|QueryBuilder
    {
        return YourModel::query()->whereBetween('created_at', [$start, $end]);
    }

    #[Override]
    public function mapTableData(LengthAwarePaginator $paginatedQuery): LengthAwarePaginator
    {
        return $paginatedQuery->through(fn($report): array => [
            'custom_field' => $report->custom_field,
            'date_field' => $report->created_at->format('Y-m-d'),
            'amount_field' => currency_format($report->amount),
        ]);
    }
}
```

### Registering report rules

Once you have defined a report rule class, you need to register it in your extension's `registerReportRules` method:

```php
public function registerReportRules(): array
{
    return [
        \Author\Extension\ReportRules\CustomReportRule::class,
    ];
}
```

### Available filter operators

When defining filters in your report rules, you can use the following operators:

#### String operators
- `equal` - Field equals the specified value
- `not_equal` - Field does not equal the specified value
- `begins_with` - Field begins with the specified value
- `not_begins_with` - Field does not begin with the specified value
- `contains` - Field contains the specified value
- `not_contains` - Field does not contain the specified value
- `ends_with` - Field ends with the specified value
- `not_ends_with` - Field does not end with the specified value
- `is_empty` - Field is empty
- `is_not_empty` - Field is not empty
- `is_null` - Field is null
- `is_not_null` - Field is not null

#### Numeric operators
- `equal` - Field equals the specified value
- `not_equal` - Field does not equal the specified value
- `less` - Field is less than the specified value
- `less_or_equal` - Field is less than or equal to the specified value
- `greater` - Field is greater than the specified value
- `greater_or_equal` - Field is greater than or equal to the specified value

#### Array operators
- `in` - Field value is in the specified array
- `not_in` - Field value is not in the specified array

### Available filter input types

When defining filters, you can specify different input types:

- `text` - Text input field
- `number` - Number input field
- `select` - Dropdown selection
- `datepicker` - Date picker input
- `checkbox` - Checkbox input
- `radio` - Radio button input

### Column types

When defining columns, you can specify different types for proper formatting:

- `string` - Default text column
- `date` - Date column with proper formatting
- `datetime` - Date and time column
- `number` - Numeric column
- `currency` - Currency column with proper formatting

### Using the Report Editor form widget

The Report Editor form widget is available for use in the Admin Panel. The widget provides a visual query builder interface for creating complex report filters.

To use the widget, add the following code to your form field definition file:

```php
'my_field' => [
    'label' => 'Report Rules',
    'type' => 'reporteditor',
],
```

### Permissions

The Reports extension registers the following permission:

- `IgniterLabs.Reports.Manage`: Control who can manage reports in the admin area.

For more on restricting access to the admin area, see the [TastyIgniter Permissions](https://tastyigniter.com/docs/customize/permissions) documentation.
