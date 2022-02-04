@extends('setup.main')
@section('content')

    <div class="row">
        <div class="col-12 text-center mt-3">
            <ul class="progressbar">
                <li class="active"><a href="/setup">Server Requirements</a></li>
                <li class="active"><a href="/setup/step-1">Settings</a></li>
                <li>Database</li>
                <li>Summary</li>
            </ul>
        </div>
    </div>

    <div class="row mt-3 p-5">
        <div class="col-12">
            <form action="{{route('setupStep1')}}" method="post">
                @csrf
                <div class="form-group">
                    <label for="app_name">Name your Application</label> <span class="tip" title="This is the name of your Application"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" id="app_name" name="app_name" placeholder="Laravel"  value="{{$data['APP_NAME']}}" autofocus>
                </div>

                <div class="form-group">
                    <label for="app_env">Select Environment</label> <span class="tip" title="The environment you want to deploy to. For coding you usually want to use 'local'"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    <select class="form-control" id="app_env" name="app_env">
                        @if($data['APP_ENV'] == 'local')
                            <option value="local">Local</option>
                            <option value="testing">Testing</option>
                            <option value="production">Production</option>
                        @elseif($data['APP_ENV'] == 'testing')
                            <option value="testing">Testing</option>
                            <option value="local">Local</option>
                            <option value="production">Production</option>
                        @else
                            <option value="production">Production</option>
                            <option value="testing">Testing</option>
                            <option value="local">Local</option>
                        @endif

                    </select>
                </div>

                <div class="form-group">
                    <label for="app_debug">App Debug Mode</label> <span class="tip" title="APP_DEBUG offers error reporting for development purpose"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    <select class="form-control" id="app_debug" name="app_debug">
                        @if($data['APP_DEBUG'] == 'true')
                        <option value="true">true</option>
                        <option value="false">false</option>
                        @else
                            <option value="false">false</option>
                            <option value="true">true</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="app_name">App Key</label> <span class="tip" title="The application key is a unique base64 String. Click if you want a new one for your application"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" id="app_key" name="app_key"   value="{{$data['APP_KEY']}}" placeholder="Click Button to generate" readonly>

                    <div class="col-12 col-md-6">
                        <button class="btn btn-outline-warning mt-3" id="generate_key" title="Generate">Generate Key</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                         <a href="/setup" class="btn btn-outline-danger  mt-3"  ><i class="fa fa-angle-left"></i> Previous Step </a>
                    </div>
                   <div class="col-6 col-md-6">
                        <button type="submit" id="next"  class="btn btn-outline-danger mt-3 float-md-right" > Next Step <i class="fa fa-angle-right"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
