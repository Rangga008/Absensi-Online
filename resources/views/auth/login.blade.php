@extends('layouts.auth')

@section('content')

<!-- Outer Row -->
<div class="row justify-content-center">


    <div class="card o-hidden border-0 w-50 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <img src="https://d17ivq9b7rppb3.cloudfront.net/original/event/codepolitan_x_here_technologies_boost_your_android_app_performance_with_jetpack_and_utilize_here_maps_api_logo_071119135400.png" width="120" alt="">
                            <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                            @if(session()->has('message'))
                            <div class="alert alert-danger">
                                {{ session('message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                            @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                        </div>
                        <form class="user" method="POST" action="{{ url('login') }}">
                            @csrf
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user" name="email" placeholder="Enter Email Address...">
                                @error('email')
                                <small class="text-danger ml-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user" name="password" placeholder="Password">
                                @error('password')
                                <small class="text-danger ml-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Login
                            </button>
                        </form>
                        <hr>
                        <!-- <div class="text-center">
                            <a class="small" href="forgot-password.html">Forgot Password?</a>
                        </div> -->
                        <div class="text-center">
                            <a class="small" href="{{ url('register') }}">Create an Account!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

@endsection