@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif



                    <h2> Name  - <strong>{{Auth::user()->name}}</strong></h2>
                    <h2>Email - <strong>{{Auth::user()->email}}</strong></h2>
                    <h2>Api-Key - <strong>{{Auth::user()->api_key}}</strong></h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
