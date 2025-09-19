@extends('layouts.auth')

@section('title', 'Internal Server Error')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle fa-5x text-danger"></i>
                </div>
                <h1 class="h2 text-gray-900 mb-4">500 - Internal Server Error</h1>
                <p class="lead text-gray-600 mb-4">
                    Something went wrong on our end.
                </p>
                <p class="text-gray-500 mb-4">
                    We're working to fix this issue. Please try again later.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home mr-2"></i>Go to Home Page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
