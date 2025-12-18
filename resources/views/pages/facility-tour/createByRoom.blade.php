<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form Facility Tour By Room</title>
    <link rel="stylesheet" href="{{ asset('/assets/bootstrap-5.0.2/css/bootstrap.min.css') }}">
    {{-- swal --}}
  <script src="{{ asset('/assets/js/swal.js') }}"></script>
</head>
<body class="mb-3" style="background:#e8f8f1">
    <div class="container mt-3">
        <div class="header-breadcrumb">
            <h2 id="page-title">Facility Tour</h2>
            <div class="breadcrumb" id="breadcrumb">
                <span>Facility</span> / Input Facility Tour
            </div>
        </div>

    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #2b6b55, #19A850, #488873);"><h3 class="text-white">Form Facility Tour</h3></div>
        <div class="card-body">

            <form id="facility-tour-form" action="{{ route('facility-tour.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="qr" class="form-control" value="1">
                <div class="mb-3">
                    <label for="pelapor" class="form-label">Pelapor</label>
                    <input type="text" name="pelapor" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Ruangan</label>
                    <select name="room_id" id="room_id" class="form-control" required readonly>
                        <option value="{{$rooms->id}}">{{$rooms->name}} {{$rooms->floor}}</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="risk_grading" class="form-label">Risk Grading</label>
                    <select name="risk_grading" class="form-select" required>
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Pilih Departemen</label>
                    <select name="department_id" class="form-select" required>
                        <option value="" selected hidden>-- Pilih Departement --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Judul Laporan</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Detail Laporan</label>
                    <textarea name="detail" id="" class="form-control" cols="30" rows="10" required></textarea>
                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan Facility Tour</button>
            </form>

        </div>
    </div>

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    </div>

    <script src="{{ asset('/assets/bootstrap-5.0.2/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/jquery.js')}}"></script>
<script>
    $('#facility-tour-form').on('submit', function(e){
        e.preventDefault();

        Swal.fire({
            title: "Simpan Facility Tour?",
            text: "Pastikan data sudah benar.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if(result.isConfirmed){
                
                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('facility-tour.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response){
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Facility Tour berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            const roomId = document.getElementById('room_id').value;
                            window.location.href = `/facility-tour/create-by-room/${roomId}`;
                        }, 2000);
                    },

                    error: function(xhr){
                        let message = "Terjadi kesalahan.";
                        if(xhr.responseJSON && xhr.responseJSON.message){
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: message,
                        });
                    }
                });
            }
        });

    });
</script>
</body>
</html>
    
