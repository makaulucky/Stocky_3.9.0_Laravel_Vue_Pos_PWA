@extends('update.main')
@section('content')
    <div class="row mt-3 p-5 d-block" id="content">
        <div class="loader d-none">Loading...</div>
        <form action="{{route('update_lastStep')}}" method="post">
        @csrf
            <div class="col-12 d-flex justify-content-center">
                <button class="btn btn-success" id="update_db"> Update Database</button>
            </div>
        </form>
    </div>

</div>
@endsection
