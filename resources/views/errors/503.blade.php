@extends('layouts.auth')

@section('title', 'Service Unavailable')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-tools fa-5x text-secondary"></i>
                </div>
                <h1 class="h2 text-gray-900 mb-4">503 - Service Unavailable</h1>
                <p class="lead text-gray-600 mb-4">
                    The service is temporarily unavailable.
                </p>
                <p class="text-gray-500 mb-4">
                    We're performing maintenance or experiencing high traffic. Please try again later.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home mr-2"></i>Go to Home Page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
