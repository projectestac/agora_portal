<div class="clearfix">
    <div class="pull-left"><strong>{{ __('file.quota_usage') }}</strong>:&nbsp;</div>
    <div class="progress pull-left" style="width:200px;">
        <div class="progress-bar progress-bar-success"
             role="progressbar"
             aria-valuenow="{{ $percent }}"
             aria-valuemin="0"
             aria-valuemax="100"
             style="width: {{ $percent }}%;">
            {{ $percent }}%
        </div>
    </div>
    <div class="pull-left">
        &nbsp;&nbsp;({{ $usedQuota }} / {{ $quota }})
        &nbsp;&nbsp;<a href="{{ route('myagora.quota.recalc', ['id' => $instanceId]) }}">{{ __('common.update') }}</a>
        @if($ratio > $configQuota)
            &nbsp;&nbsp;
            <a href="{{ route('myagora.requests') }}">{{ __('request.ask_for_more_quota') }}</a>
        @endif
    </div>
</div>
