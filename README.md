# Manage approval processes in your filament application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eightynine/filament-approvals.svg?style=flat-square)](https://packagist.org/packages/eightynine/filament-approvals)
[![Total Downloads](https://img.shields.io/packagist/dt/eightynine/filament-approvals.svg?style=flat-square)](https://packagist.org/packages/eightynine/filament-approvals)

This package allows you to implement approval flows in your Laravel Filament application.

_This package brings the [ringlesoft/laravel-process-approval](https://github.com/ringlesoft/laravel-process-approval)) functionalities to filament. You can use all the ringlesoft/laravel-process-approval features in your laravel project. It also uses the [spatie/laravel-permissions](https://github.com/spatie/laravel-permissions) package, so you can use all its features._

## 🛠️ Be Part of the Journey

Hi, I'm Eighty Nine. I created aprovals plugin to solve real problems I faced as a developer. Your sponsorship will allow me to dedicate more time to enhancing these tools and helping more people. [Become a sponsor](https://github.com/sponsors/eighty9nine) and join me in making a positive impact on the developer community.

## Quick understanding the package

Some processes in your application require to be approved by multiple people before the process can be completed. For example, an employee submits a timesheet, then the supervisor approves, then manager approves and finally the HR approves and the timesheet is logged.
This package is a solution for this type of processes.

### Approval flow

This is the chain of events for a particular process. For example, timesheet submission, expense request, leave request. These processes require that multiple people have check and approve or reject, until the process is complete.

Approval flows are based on a model, example, ExpenseRequest, LeaveRequest, TimesheetLogSubmission etc

### Approval step

These are the steps that the process has. Each step is associated with a role that contains users that need to approve. When any of the users in the role approves, the process moves forward to the next step.

This package is based on roles, which are provided by the package [spatie/laravel-permission](https://github.com/spatie/laravel-permission).

## Installation

You can install the package via composer:

```bash
composer require eightynine/filament-approvals
```

## Usage

1. Run the migrations using:

```bash
php artisan migrate
```

2. Add the plugin to your panel service provider as follows:

```php

    ->plugins([
        \EightyNine\Approvals\ApprovalPlugin::make()
    ])
```

3. Make your model extend the ApprovableModel

```php

namespace App\Models;

use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends ApprovableModel
{
    use HasFactory;

    protected $fillable = ["name"];
}

```

4. Create approval flows
- In your dashboard, a "Approval flows menu will have appeared". Click it and start creating the approval flows. The name is the name of the model, that you are using in your flow.

- After you create your first approval create the steps. The steps will require that you have already create roles in your admin panel using the spatie/laravel-permission package.

- You can move to the next step 😉

5. Add the approvable actions:

- In your resource table, add the approvable actions

```php
$table
    ->actions(
        ...\EightyNine\Approvals\Tables\Actions\ApprovalActions::make(
            // define your action here that will appear once approval is completed
            Action::make("Done"),
            [
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
            ]
        ),
    )

```

- In your view page or edit page, you can include the approval actions using the trait HasApprovalHeaderActions, and define the method getOnCompletionAction() that will return the action(s) to be shown once complete. If this method is not implemented and you use the trait, an error will be thrown.

```php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaveRequest extends ViewRecord
{
    use  \EightyNine\Approvals\Traits\HasApprovalHeaderActions;

    protected static string $resource = LeaveRequestResource::class;


    /**
     * Get the completion action.
     *
     * @return Filament\Actions\Action
     * @throws Exception
     */
    protected function getOnCompletionAction(): Action
    {
        return Action::make("Done")
            ->color("success")
            // Do not use the visible method, since it is being used internally to show this action if the approval flow has been completed.
            // Using the hidden method add your condition to prevent the action from being performed more than once
            ->hidden(fn(ApprovableModel $record)=> $record->shouldBeHidden())
    }
}

```

6. Add the ApprovalStatusColumn to your table to see the status of the approval flow

```php
    return $table
        ->columns([
            TextColumn::make("name"),
            \EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn::make("approvalStatus.status"),
        ])
    ...
```

Just like that, you are good to go, make some moneyyyyy🤑

To add more approval flows(models), repeat the steps 3-6

## 🎨 Customization & Publishing

This package provides extensive customization options by publishing various components. You can publish and customize configuration files, views, Filament resources, form/table components, translations, and more.

### Quick Publishing

Use the custom publish command for an interactive publishing experience:

```bash
php artisan approvals:publish
```

This will show you an interactive menu to choose what you want to publish.

### Publishing Specific Components

You can also publish specific components using command options:

#### Configuration File
```bash
php artisan approvals:publish --config
```
This publishes the configuration file to `config/approvals.php` where you can customize:
- Role model configuration
- Navigation settings (icon, sort order, visibility)
- Comment settings for approvals and rejections

#### View Files
```bash
php artisan approvals:publish --views
```
This publishes all Blade view files to `resources/views/vendor/filament-approvals/` for complete UI customization:
- `tables/columns/approval-status-column.blade.php` - Customize the approval status display
- `tables/columns/approval-status-column-action-view.blade.php` - Customize approval history view

#### Filament Resources
```bash
php artisan approvals:publish --resources
```
This publishes Filament resources to `app/Filament/Resources/` allowing you to:
- Customize the ApprovalFlowResource completely
- Modify forms, tables, and pages
- Add custom validation and business logic

#### Form & Table Components
```bash
php artisan approvals:publish --components
```
This publishes reusable components to `app/Forms/Approvals/` and `app/Tables/Approvals/`:
- Custom approval action forms
- Specialized table columns and actions
- Approval workflow components

#### Translation Files
```bash
php artisan approvals:publish --translations
```
This publishes language files to `resources/lang/vendor/filament-approvals/` for localization:
- Customize all text and messages
- Add support for additional languages
- Modify approval status terminology

#### Development Stubs
```bash
php artisan approvals:publish --stubs
```
This publishes stub files to `stubs/filament-approvals/` for development and extension.

#### Publish Everything
```bash
php artisan approvals:publish --all
```
This publishes all customizable files at once.

### Configuration Options

After publishing the config file, you can customize these settings in `config/approvals.php`:

```php
return [
    // Specify your role model (must be compatible with spatie/laravel-permission)
    "role_model" => App\Models\Role::class,
    
    // Navigation configuration
    "navigation" => [
        "should_register_navigation" => true,
        "icon" => "heroicon-o-clipboard-document-check",
        "sort" => 1
    ],
    
    // Comment settings
    "enable_approval_comments" => false, // Allow comments when approving
    "enable_rejection_comments" => true, // Allow comments when rejecting
];
```

### Customizing Views

After publishing views, you can completely customize the appearance:

**Approval Status Column (`resources/views/vendor/filament-approvals/tables/columns/approval-status-column.blade.php`)**:
- Modify status display logic
- Customize styling and colors
- Add additional status information

**Approval History View (`resources/views/vendor/filament-approvals/tables/columns/approval-status-column-action-view.blade.php`)**:
- Customize approval history display
- Modify user avatar and information layout
- Enhance comment formatting

### Extending Filament Resources

When you publish the Filament resources, you gain full control:

```php
// In your published ApprovalFlowResource
class ApprovalFlowResource extends Resource
{
    // Add custom form fields
    public static function form(Form $form): Form
    {
        return $form->schema([
            // ... existing fields ...
            
            // Add your custom fields
            TextInput::make('custom_field')
                ->label('Custom Configuration'),
        ]);
    }
    
    // Customize table columns
    public static function table(Table $table): Table
    {
        return $table->columns([
            // ... existing columns ...
            
            // Add custom columns
            TextColumn::make('custom_data')
                ->label('Custom Information'),
        ]);
    }
}
```

### Advanced Customization Tips

1. **Custom Approval Actions**: Extend the published form/table components to add custom approval logic
2. **Styling**: Use the published views to match your application's design system
3. **Localization**: Publish translations and add your language files
4. **Business Logic**: Modify the published resources to add organization-specific workflows

### Best Practices

- Always backup your customizations before updating the package
- Use version control to track your customized files
- Test customizations thoroughly in a development environment
- Document your customizations for team members

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Eighty Nine](https://github.com/eighty9nine)
-   [Tony Partridge](https://github.com/tonypartridge)
-   [Ringlesoft](https://github.com/ringlesoft/laravel-process-approval) for the base approval model logic
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
