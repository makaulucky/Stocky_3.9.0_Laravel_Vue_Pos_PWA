@extends('setup.main')
@section('content')
<meta name="csrf_token" content="{{ csrf_token() }}" />


    <div class="row">
        <div class="col-12 text-center mt-3">
            <ul class="progressbar">
                <li class="active">Server Requirements</li>
                <li class="active"><a href="/setup">Settings</a></li>
                <li class="active"><a href="/setup/step-2">Database</a></li>
                <li class="active"><a href="/setup/step-3">Summary</a></li>
            </ul>
        </div>
    </div>

    <div class="row mt-3">
        <div class="loader d-none">Loading...</div>
    </div>

    <div class="row mt-3 p-5 d-block" id="content">

        <div class="col-12">

            <form  action="{{route('lastStep')}}" method="post">
                @csrf

                <h2 class="mb-5">Do you want these settings to change?</h2>

                <div id="tochange">

                @if($data['APP_NAME'] != 'old')
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 text-truncate">Application Name</div>

                            <div class="col-12 col-md-6 text-truncate"> {{ $data['APP_NAME'] }}</div>
                        </div>
                    </div>
                @endif

                @if($data['APP_KEY'] != 'old')
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-md-6 text-truncate font-weight-bold">Application Key</div>

                        <div class="col-12 col-md-6 text-truncate"> {{ $data['APP_KEY'] }}</div>
                    </div>
                </div>
                @endif

                @if($data['APP_DEBUG'] != 'old')
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 text-truncate ">Application Debug Mode</div>

                            <div class="col-12 col-md-6 text-truncate"> {{ $data['APP_DEBUG'] }}</div>
                        </div>
                    </div>
                @endif


                @if($data['DB_HOST'] != 'old')
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 text-truncate">Database Host</div>

                            <div class="col-12 col-md-6 text-truncate"> {{ $data['DB_HOST'] }}</div>
                        </div>
                    </div>
                @endif


                @if($data['DB_DATABASE'] != 'old')
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 text-truncate">Database Selected</div>

                            <div class="col-12 col-md-6 text-truncate"> {{ $data['DB_DATABASE']}}</div>
                        </div>
                    </div>
                @endif

                @if($data['DB_USERNAME'] != 'old')
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 text-truncate">Database Username</div>

                            <div class="col-12 col-md-6 text-truncate"> {{ $data['DB_USERNAME'] }}</div>
                        </div>
                    </div>
                @endif


                </div>
                <div class="row mt-5">
                    <div class="col-12 col-md-6 text-truncate">
                        <a href="/setup/step-2" class="btn btn-outline-danger mb-2"  ><i class="fa fa-angle-left"></i> Previous Step </a>
                    </div>
                    <div class="col-12 col-md-6 text-truncate">
                        <button  type="submit" class="btn btn-success mb-2 btn-block" id="lastStep"  >Confirm <i class="fa fa-check"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
