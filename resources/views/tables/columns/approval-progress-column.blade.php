<div class="approval-progress-column">
    @php
        $percentage = $getProgressPercentage($getRecord());
        $currentStep = $getCurrentStep($getRecord());
        $status = $getStepStatus($getRecord());
        $color = $getProgressColor($getRecord());
    @endphp

    <div class="space-y-2">
        {{-- Progress Bar --}}
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-300 
                @switch($color)
                    @case('success')
                        bg-green-500
                        @break
                    @case('warning')
                        bg-yellow-500
                        @break
                    @case('danger')
                        bg-red-500
                        @break
                    @default
                        bg-gray-400
                @endswitch
            " style="width: {{ $percentage }}%"></div>
        </div>

        {{-- Status Info --}}
        <div class="flex items-center justify-between text-xs">
            <span class="font-medium text-gray-700">
                {{ $percentage }}% Complete
            </span>
            
            @if($currentStep)
                <span class="text-gray-500">
                    @if($status === 'completed')
                        ✅ {{ $currentStep }}
                    @elseif($status === 'pending')
                        ⏳ Awaiting: {{ $currentStep }}
                    @elseif($status === 'rejected')
                        ❌ Rejected
                    @elseif($status === 'discarded')
                        🗑️ Discarded
                    @else
                        📝 {{ $currentStep }}
                    @endif
                </span>
            @endif
        </div>
    </div>
</div>
