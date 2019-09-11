@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card bg-light mt-3">
        <div class="card-header">
            Product data collection system
        </div>
        <div class="card-body">
            @include('errors.errorlist')
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" class="form-control">
                <br>
                <button class="btn btn-success">Import Stores</button>
                @if(count($stores) > 0)
                    <a class="btn btn-warning" href="{{ route('export') }}">Export Products Data</a>
                @endif
            </form>
        </div>
    </div>
</div>

@endsection