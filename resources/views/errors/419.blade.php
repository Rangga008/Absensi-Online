@extends('layouts.auth')

@section('title', 'Page Expired')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-clock fa-5x text-info"></i>
                </div>
                <h1 class="h2 text-gray-900 mb-4">419 - Page Expired</h1>
                <p class="lead text-gray-600 mb-4">
                    Your session has expired due to inactivity.
                </p>
                <p class="text-gray-500 mb-4">
                    For security reasons, please refresh the page and try again.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home mr-2"></i>Go to Home Page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
