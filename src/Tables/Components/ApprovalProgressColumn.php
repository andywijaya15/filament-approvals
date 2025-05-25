<?php

namespace EightyNine\Approvals\Tables\Components;

use Filament\Tables\Columns\Column;
use Filament\Support\Concerns\HasColor;
use Illuminate\Database\Eloquent\Model;

class ApprovalProgressColumn extends Column
{
    use HasColor;

    protected string $view = 'filament-approvals::tables.columns.approval-progress-column';

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Approval Progress');
    }

    public function getProgressPercentage(Model $record): int
    {
        if (!$record->approvalStatus) {
            return 0;
        }

        $totalSteps = $record->approvalFlow?->steps()->count() ?? 1;
        $completedSteps = $record->approvals()->where('approval_action', 'Approved')->count();

        return min(100, round(($completedSteps / $totalSteps) * 100));
    }

    public function getCurrentStep(Model $record): ?string
    {
        if (!$record->approvalStatus) {
            return null;
        }

        if ($record->isApprovalCompleted()) {
            return 'Completed';
        }

        return $record->nextApprover?->name ?? 'Unknown';
    }

    public function getStepStatus(Model $record): string
    {
        if (!$record->approvalStatus) {
            return 'not-started';
        }

        if ($record->isApprovalCompleted()) {
            return 'completed';
        }

        return match ($record->approvalStatus->status) {
            'Pending' => 'pending',
            'Approved' => 'approved',
            'Rejected' => 'rejected',
            'Discarded' => 'discarded',
            default => 'unknown',
        };
    }

    public function getProgressColor(Model $record): string
    {
        return match ($this->getStepStatus($record)) {
            'completed', 'approved' => 'success',
            'pending' => 'warning',
            'rejected', 'discarded' => 'danger',
            default => 'gray',
        };
    }
}
