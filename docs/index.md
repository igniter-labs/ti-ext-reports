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
2. Create a new report
3. Fill in the report details and the rules for the report and save.

### Displaying reports
1. Go to **Dashboard** in the admin area.
2. Click on the **+ Add Widget** button.
3. Select **Smart Reports** and save.
4. Click on the **⚙️ / Edit Widget** button on the widget.
5. From the **Report** dropdown, select the report you created earlier and save.
6. You should now see the report data displayed in the widget.

## Features
- Charts showing report data such as customers with most orders, best-selling products, etc.
- Data tables showing detailed report data such as orders, products, customers, etc.
- Filters to customize the report data. 

and many more...
