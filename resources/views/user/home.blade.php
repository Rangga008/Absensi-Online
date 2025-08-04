@extends('user.layouts')

@section('content')

@if(session()->has('message'))
<div class="alert alert-success">
    {{ session('message') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div>
    Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi PT. Codepolitan Integrasi Indonesia. <br />
    <small>Kamis, 27 Juni 2021</small>
</div>

<div id="carouselExampleIndicators" class="carousel slide mt-3" data-ride="carousel">
    <ol class="carousel-indicators">
        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img class="d-block w-100" src="https://images.pexels.com/photos/3184357/pexels-photo-3184357.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="First slide">
        </div>
        <div class="carousel-item">
            <img class="d-block w-100" src="https://images.pexels.com/photos/840996/pexels-photo-840996.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="Second slide">
        </div>
        <div class="carousel-item">
            <img class="d-block w-100" src="https://images.pexels.com/photos/2041627/pexels-photo-2041627.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="Third slide">
        </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<hr />

<div class="mt-3">
    <a href="{{ url('user/attendance') }}" class="btn btn-primary btn-block m-1">Absen Online</a>
    <a href="{{ url('user/concession') }}" class="btn btn-primary btn-block m-1">Izin Online</a>
    <a href="{{ url('user/salary') }}" class="btn btn-primary btn-block m-1">Lihat Gaji</a>
    <a href="{{ url('user/history') }}" class="btn btn-primary btn-block m-1">History Absen</a>
    <a href="{{ url('user/logout') }}" class="btn btn-danger btn-block m-1 mt-4">Logout</a>
</div>

@endsection