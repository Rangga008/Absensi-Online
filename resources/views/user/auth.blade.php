<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">

    <title>Aplikasi Absensi Berbasis Lokasi</title>
</head>

<body style="background-color: teal;">

    <div class="container">
        <div class="content mt-5 col-md-4 offset-md-4">
            <div class="card p-4">
                <div class="mb-3">
                    <h2>Sistem Presensi Online</h2>
                    <small>PT. Codepolitan Integrasi Indonesia</small>
                </div>
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
                <form class="mt-4" action="{{ url('user/login') }}" method="POST">
                    @CSRF
                    <div class="form-group">
                        <input type="text" name="email" class="form-control" placeholder="Email" />
                        @error('email')
                        <small class="text-danger ml-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" />
                        @error('password')
                        <small class="text-danger ml-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success">Login</button>
                </form>

            </div>

        </div>
    </div>

    <div id="footer">
        <div class="text-center my-3">
            Copyright &copy; 2021 Alfian Pratama
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>