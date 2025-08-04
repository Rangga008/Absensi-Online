<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Custom fonts for this template-->
    <link href="{{ asset('sbadmin') }}/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('sbadmin') }}/css/sb-admin-2.min.css" rel="stylesheet">

    <title>Aplikasi Absensi Berbasis Lokasi</title>
</head>

<body style="background-color: #f7f7f7;">

    @include('user.navbar')

    <div class="container">

        <div class="content mt-5 col-md-4 offset-md-4">
            @yield('content')
        </div>
    </div>

    <div id="footer">
        <div class="text-center mt-5">
            Copyright &copy; 2021 Alfian Pratama
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('sbadmin') }}/vendor/jquery/jquery.min.js"></script>
    <script src="{{ asset('sbadmin') }}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('sbadmin') }}/vendor/jquery-easing/jquery.easing.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#attendance').click(function() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        // $("#result").html("Your location is <br/> Lat : " + position.coords.latitude + " <br/> Lang : " + position.coords.longitude);
                        let lat1 = -6.8789533;
                        let long1 = 107.5891104;
                        let lat2 = -6.8794756;
                        let long2 = 107.5877356;
                        getDistanceFromLatLonInKm(lat1, long1, lat2, long2)
                    })
                } else {
                    console.log("Browser not support");
                }
            })
        })

        function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
            var R = 6371; // Radius of the earth in km
            var dLat = deg2rad(lat2 - lat1); // deg2rad below
            var dLon = deg2rad(lon2 - lon1);
            var a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c; // Distance in km
            if (Math.floor(d) > 1) {
                $("#msg").html("<div class='alert alert-danger'>You are out of range! <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>")
            } else {
                let user_id = $("#user_id").val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    type: "POST",
                    url: "/doAttendance",
                    data: {
                        user_id
                    },
                    cache: false,
                    success: function(data) {
                        if(data.status == true) {
                        $("#msg").html("<div class='alert alert-warning'>" + data.message + " <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>")
                        } else {
                        $("#msg").html("<div class='alert alert-success'>" + data.message + " <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>")
                        }
                    }
                })
            }
        }

        function deg2rad(deg) {
            return deg * (Math.PI / 180)
        }
    </script>


</body>

</html>