@extends('layouts.auth')

@section('title', 'Page Not Found')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-search fa-5x text-warning"></i>
                </div>
                <h1 class="h2 text-gray-900 mb-4">404 - Page Not Found</h1>
                <p class="lead text-gray-600 mb-4">
                    The page you're looking for doesn't exist.
                </p>
                <p class="text-gray-500 mb-4">
                    It might have been moved, deleted, or you entered the wrong URL.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home mr-2"></i>Go to Home Page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
