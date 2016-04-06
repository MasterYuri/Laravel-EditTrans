
@extends($layout)


@section($section_styles)
	@parent
    
    @if ($load_bootstrap3)
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    @endif
@endsection


@section($section_content)
	@parent
    <h1>
        File 
        <span class='label label-primary'>{{ $path }}</span>
        language 
        <span class='label label-success'>{{ $langs[$lang] }}</span>
    </h1>
	<hr>
	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

    <form class="form-vertical" role="form" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	
		@if ($allow_save_for_lang)
			<div class="well">
				<label for="ii-lang">Save for language</label>
				<select class="form-control" name="___save_lang" id="ii-lang">
					@foreach ($langs as $l => $title)
						<option value="<?= $l?>"  <?= Request::input("___save_lang", $l) == $lang ? "selected" : ""?> >{{ $title }}</option>
					@endforeach
				</select>
			</div>
		@endif
        
        @include('edit-trans::edit.text', ['vars' => $vars])
        
        <div class="form-group">
            <button class="btn btn-lg btn-primary">Save</button>
            <a href="{{ action('\MasterYuri\EditTrans\Controller@pageList') }}" class="btn btn-lg btn-default">Cancel</a>
        </div>
    </form>
@endsection


@section($section_scripts)
	@parent

    @if ($load_jquery)
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    @endif
    @if ($load_bootstrap3)
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    @endif
    @if ($load_ckeditor4)
    	<script src="{{ asset('vendor/masteryuri/edit-trans/js/ckeditor4.js') }}" type="text/javascript"></script>
    @endif                     

	<script type="text/javascript">
        $g_editTransUploadUrl = "{{ $richedit_upload_url }}";
    </script>
	<script src="//cdn.ckeditor.com/4.5.7/standard/ckeditor.js" type="text/javascript"></script>
@endsection

