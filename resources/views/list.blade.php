
@extends($layout)


@section($section_styles)
	@parent
    
    @if ($load_bootstrap3)
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    @endif
@endsection


@section($section_content)
    <h1>All translations</h1>
    <p>List of all project translations.</p>
    
    @if ($msg)
        <div class="alert alert-success">
            {{ $msg }}
        </div>
    @endif

    <div class-disable="table-responsive">
        <table class="table table-striped table-hover table-condensed">
            <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th>
                        Path
                    </th>
                    @if ($single)
                        <th width=170>
                        </th>
                    @else
                        @foreach ($langs as $l)
                            <th width=1>
                            </th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($paths as $i => $path)
                    <tr class="{{ $path == $edited ? 'success' : '' }}">
                        <th>
                            {{ $i + 1 }}
                        </th>
                        <td>
                            {{ $path }}
                        </td>
                        @if ($single)
                            <td width=170>
                                <div class="btn-group btn-block">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle btn-block" data-toggle="dropdown" aria-expanded="false">
                                        Edit <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                        @foreach ($langs as $lang => $title)
                                            <li>
                                                <a href="{{ action('\MasterYuri\EditTrans\Controller@pageEdit', ['path' => $path, 'lang' => $lang]) }}">
                                                    {{ $title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        @else
                            @foreach ($langs as $lang => $title)
                                <td width=1>
                                    <a href="{{ action('\MasterYuri\EditTrans\Controller@pageEdit', ['path' => $path, 'lang' => $lang]) }}" class="btn btn-sm btn-primary">
                                        {{ $title }}
                                    </a>
                                </td>
                            @endforeach
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection


@section($section_scripts)
	@parent

    @if ($load_jquery)
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    @endif
    @if ($load_bootstrap3)
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    @endif
@endsection
