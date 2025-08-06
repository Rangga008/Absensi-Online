@extends('layouts.auth')

@section('content')
<div class="d-flex justify-content-center">
    <div class="card o-hidden border-0 w-50 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <img src="https://d17ivq9b7rppb3.cloudfront.net/original/event/codepolitan_x_here_technologies_boost_your_android_app_performance_with_jetpack_and_utilize_here_maps_api_logo_071119135400.png" width="120" alt="">
                            <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                        </div>
                        <form class="user" method="POST" action="{{ url('register') }}">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" name="name" placeholder="Name" value="{{ old('name') }}">
                                    @error('name')
                                    <small class="text-danger ml-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-user" name="phone" placeholder="Phone" value="{{ old('phone') }}">
                                    @error('phone')
                                    <small class="text-danger ml-2">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user" name="email" placeholder="Email" value="{{ old('email') }}">
                                @error('email')
                                <small class="text-danger ml-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user" name="address" placeholder="Address" value="{{ old('address') }}">
                                @error('address')
                                <small class="text-danger ml-2">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" class="form-control form-control-user" name="password" placeholder="Password">
                                    @error('password')
                                    <small class="text-danger ml-2">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control form-control-user" name="repeat_password" placeholder="Repeat Password">
                                    @error('repeat_password')
                                    <small class="text-danger ml-2">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Register Account
                            </button>
                        </form>
                        <hr>
                        <!-- <div class="text-center">
                            <a class="small" href="forgot-password.html">Forgot Password?</a>
                        </div> -->
                        <div class="text-center">
                            <a class="small" href="{{ url('/admin') }}">Already have an account? Login!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection