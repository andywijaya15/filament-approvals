<div>
    @if($getRecord()->approvalStatus)
        <p class="px-3">
            <small>
                @if ($getRecord()->isApprovalCompleted())
                    {{ __('filament-approvals::approvals.status_column.approval_complete') }} {{ __('filament-approvals::approvals.status_column.approval_by_prefix') }}
                    @if ($getRecord()->lastApproval)
                        {{ $getRecord()->lastApproval->approver_name }}
                    @else
                        {{ $getRecord()->createdBy()->name }}
                    @endif
                @else
                    {{ $getRecord()->approvalStatus->status }} {{ __('filament-approvals::approvals.status_column.approval_by_prefix') }}
                    @if ($getRecord()->nextApprover)
                        {{ $getRecord()->nextApprover->name }}
                    @else
                        {{ $getRecord()->createdBy()->name }}
                    @endif
                @endif
            </small>
        </p>
        <p class="px-3 text-xs">
            <small>
                {{ $getRecord()->isApprovalCompleted() ?
                    __('filament-approvals::approvals.status_column.approval_complete') :
                    __('filament-approvals::approvals.status_column.approval_in_process') }}
            </small>
        </p>
    @else
        <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs">
            {{ __('filament-approvals::approvals.status_column.approval_status_does_not_exist') }}
        </span>
    @endif
</div>
