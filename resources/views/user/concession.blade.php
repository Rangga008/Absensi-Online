@extends('user.layouts')

@section('content')

<div>
    Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi PT. Codepolitan Integrasi Indonesia. <br />
    <small>{{ date('D, d F Y') }}</small>
</div>

<hr />

<form action="{{ url('user/concession') }}" method="POST">
    @CSRF
    <div class="form-group">
        <label>Tidak hadir karena?</label>
        <select class="form-control" name="reason">
            <option value="cuti">Cuti</option>
            <option value="sakit">Sakit</option>
            <option value="izin">Izin</option>
        </select>
    </div>
    <div class="form-group">
        <label>Mohon tambahkan keterangan lengkap</label>
        <textarea class="form-control" name="description" placeholder="Tambahkan keterangan"></textarea>
        @error('description')
        <small class="text-danger ml-1">{{ $message }}</small>
        @enderror
    </div>
    <div class="mb-3 text-right">
        <button type="submit" class="btn btn-success mt-3">Kirim Izin</button>
    </div>
</form>


<a href="{{ url('user/home') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>

@endsection