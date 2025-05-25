<?php

namespace EightyNine\Approvals\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;

class ApprovalFormBuilder extends Component
{
    protected string $view = 'filament-approvals::forms.components.approval-form-builder';

    public static function make(): static
    {
        return app(static::class);
    }

    public function getChildComponents(): array
    {
        return [
            Section::make('Approval Configuration')
                ->description('Configure the approval workflow for this process')
                ->schema([
                    Select::make('approval_flow_id')
                        ->label('Approval Flow')
                        ->relationship('approvalFlow', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->maxLength(1000),
                        ]),

                    Toggle::make('require_comments')
                        ->label('Require Comments')
                        ->helperText('Force approvers to add comments when approving/rejecting')
                        ->default(false),

                    Select::make('priority')
                        ->label('Priority Level')
                        ->options([
                            'low' => 'Low',
                            'normal' => 'Normal',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->default('normal'),

                    Toggle::make('auto_submit')
                        ->label('Auto Submit')
                        ->helperText('Automatically submit for approval when created')
                        ->default(true),
                ])
                ->collapsible()
                ->collapsed(false),
        ];
    }
}
