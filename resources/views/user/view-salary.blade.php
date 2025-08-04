@extends('user.layouts')

@section('content')

<div>
    Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi PT. Codepolitan Integrasi Indonesia. <br />
    <small>{{ date('D, d F Y') }}</small>
</div>

<hr />

<div class="mb-3">Gaji bulan ini Rp. {{ number_format($salary_now->amount) }},-</div>

<table class="table table-condensed table-striped">
    <tr>
        <th>Bulan</th>
        <th>Tahun</th>
        <th>Jumlah</th>
    </tr>
    @foreach($salary_month as $salary)
    <tr>
        <td>{{ $salary->month }}</td>
        <td>{{ $salary->year }}</td>
        <td>Rp. {{ number_format($salary->amount) }}</td>
    </tr>
    @endforeach
</table>

<a href="{{ url('user/home') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>

@endsection