<div class="alert alert-info">{!! $requestDetails['description'] !!}</div>
<div class="form-group clearfix row">
    <label class="col-xs-12 col-sm-4 text-right" for="user-comment">{{ $requestDetails['prompt'] }}</label>
    <div class="col-xs-12 col-sm-8">
        <textarea class="form-control" id="user-comment" name="user-comment" rows="3"></textarea>
    </div>
</div>
<div class="text-center">
    <button type="submit" class="btn btn-success" title="{{ __('common.add') }}">
        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {{ __('common.add') }}
    </button>
    <a class="btn btn-danger" href="{{ route('myagora.requests') }}" title="{{ __('common.cancel') }}">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {{ __('common.cancel') }}
    </a>
</div>
