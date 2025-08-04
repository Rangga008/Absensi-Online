@extends('user.layouts')

@section('content')

<div>
    Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi PT. Codepolitan Integrasi Indonesia. <br />
    <small>{{ date('D, d F Y') }}</small>
</div>

<hr />

<div class="mb-3">

    <div class="mb-3">History seminggu terakhir</div>

    @foreach($histories as $history)

    <?php
    if (in_array($history->present_at, ['cuti', 'sakit', 'izin'])) {
    ?>

        <div class="media mb-3">
            <div class="mr-3"><span>❌</span></div>
            <div class="media-body">
                <h6 class="mt-0">{{ ucfirst($history->present_at) }}</h6>
                {{ $history->created_at }}
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="media mb-3">
            <div class="mr-3"><span>✅</span></div>
            <div class="media-body">
                <h6 class="mt-0">Hadir</h6>
                {{ $history->created_at }}
            </div>
        </div>
    <?php
    }
    ?>
    @endforeach


    <!-- <div class="media mb-3">
        <div class="mr-3"><span>❌</span></div>
        <div class="media-body">
            <h6 class="mt-0">Cuti</h6>
            Bandung, Jumat 20 Juli 2021 07:00
        </div>
    </div>

    <div class="media mb-3">
        <div class="mr-3"><span>❌</span></div>
        <div class="media-body">
            <h6 class="mt-0">Sakit</h6>
            Bandung, Jumat 21 Juli 2021 07:00
        </div>
    </div> -->
</div>

<a href="{{ url('user/home') }}" class="btn btn-sm btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>

@endsection