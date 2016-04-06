        
@foreach ($vars as $key => $info)
	<div class="form-group">
        @if (!empty($info['data']))
	      	<label for="i-{{ $key }}">
	      		{{ $info['title'] }}
	      	</label>
	      	@if ($info['data']['use_rich_edit'])
	      		<textarea id="i-{{ $key }}" class="form-control js-edit-trans-ckeditor4" mode="full" name="{{ $key }}">{{ Request::input($key, $info['data']['value']) }}</textarea>
	      	@else
	      		<textarea id="i-{{ $key }}" class="form-control" name="{{ $key }}">{{ Request::input($key, $info['data']['value']) }}</textarea>
	      	@endif
        @elseif (!empty($info['list']))
            <div class="panel panel-default">
                <div class="panel-heading"><strong>{{ $info['title'] }}</strong></div>
                <div class="panel-body">
                    @include('edit-trans::edit.text', ['vars' => $info['list']])
                </div>
            </div>
        @endif
	</div>
@endforeach
