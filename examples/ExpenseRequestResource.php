<?php

namespace App\Filament\Resources;

use App\Models\ExpenseRequest;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use EightyNine\Approvals\Tables\Components\ApprovalProgressColumn;
use EightyNine\Approvals\Forms\Components\ApprovalFormBuilder;
use EightyNine\Approvals\Traits\HasApprovalFormActions;
use EightyNine\Approvals\Traits\HasApprovalWidget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Example Resource showing how to use published Filament Approvals components
 * 
 * This file would be created by the user after publishing the components.
 * It demonstrates best practices for integrating approval workflows.
 */
class ExpenseRequestResource extends Resource
{
    use HasApprovalWidget;

    protected static ?string $model = ExpenseRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'travel' => 'Travel',
                                'meals' => 'Meals & Entertainment',
                                'supplies' => 'Office Supplies',
                                'software' => 'Software & Tools',
                                'other' => 'Other',
                            ]),

                        Forms\Components\DatePicker::make('expense_date')
                            ->required()
                            ->default(today()),

                        Forms\Components\FileUpload::make('receipt')
                            ->label('Receipt/Invoice')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120), // 5MB
                    ])
                    ->columns(2),

                // Use the published approval form builder
                ApprovalFormBuilder::make(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'travel',
                        'success' => 'meals',
                        'warning' => 'supplies',
                        'info' => 'software',
                        'secondary' => 'other',
                    ]),

                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),

                // Use the published approval progress column
                ApprovalProgressColumn::make('approval_progress'),

                // Use the standard approval status column
                ApprovalStatusColumn::make('approvalStatus.status')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'travel' => 'Travel',
                        'meals' => 'Meals & Entertainment',
                        'supplies' => 'Office Supplies',
                        'software' => 'Software & Tools',
                        'other' => 'Other',
                    ]),

                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->relationship('approvalStatus', 'status')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add approval history relation manager if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseRequests::route('/'),
            'create' => Pages\CreateExpenseRequest::route('/create'),
            'view' => Pages\ViewExpenseRequest::route('/{record}'),
            'edit' => Pages\EditExpenseRequest::route('/{record}/edit'),
        ];
    }
}

// Example Pages

namespace App\Filament\Resources\ExpenseRequestResource\Pages;

use App\Filament\Resources\ExpenseRequestResource;
use EightyNine\Approvals\Traits\HasApprovalHeaderActions;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpenseRequest extends ViewRecord
{
    use HasApprovalHeaderActions;

    protected static string $resource = ExpenseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            // Approval actions are automatically added by the trait
        ];
    }

    /**
     * Define what happens when approval process is completed
     */
    protected function getOnCompletionAction(): Action
    {
        return Actions\Action::make('process_payment')
            ->label('Process Payment')
            ->color('success')
            ->icon('heroicon-o-credit-card')
            ->requiresConfirmation()
            ->action(function () {
                // Process the expense payment
                $this->record->update(['status' => 'processing_payment']);
                
                Notification::make()
                    ->title('Payment Processing')
                    ->body('Expense request has been sent for payment processing.')
                    ->success()
                    ->send();
            });
    }
}

class CreateExpenseRequest extends CreateRecord
{
    use HasApprovalFormActions;

    protected static string $resource = ExpenseRequestResource::class;

    protected function getCreateFormAction(): Action
    {
        return $this->getSubmitFormAction()
            ->label('Submit for Approval');
    }
}

class EditExpenseRequest extends EditRecord
{
    use HasApprovalFormActions;

    protected static string $resource = ExpenseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
