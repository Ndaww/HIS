@extends('layouts.app')
@section('title','Ticketing - Buat Tiket')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Buat Tiket</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Ticketing</span> / Buat Tiket</div>
    </div>

    <div class="card">
        <div class="card-header">Buat Tiket</div>
        <div class="card-body">
        <form id="ticket-form" action="{{ route('ticketing.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">Judul Masalah</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi Masalah</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Prioritas</label>
                <select name="priority" class="form-select" required>
                    <option value="low">Rendah</option>
                    <option value="medium">Sedang</option>
                    <option value="high">Tinggi</option>
                </select>
            </div>

            {{-- <input type="hiddn" name="requester_id" value="{{ auth()->user()->id }}"> --}}

            <div class="mb-3">
                <label for="department_id" class="form-label">Pilih Departemen</label>
                <select name="department_id" class="form-select" required>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Input File Section --}}
            <div class="mb-3">
                <label class="form-label">Lampiran (Max 3 file)</label>
                <div id="file-wrapper"></div>
                <button type="button" id="add-file-btn" class="btn btn-outline-success mt-2">+ Tambah Lampiran</button>
            </div>
            <button type="submit" class="btn btn-success">Kirim Tiket</button>
        </form>

        </div>
        </div>
        @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    </div>

@endsection

@section('js')
    <script>
    let fileCount = 0;
    const maxFiles = 3;
    const fileWrapper = document.getElementById('file-wrapper');
    const addFileBtn = document.getElementById('add-file-btn');

    function updateFileCount() {
        fileCount = fileWrapper.querySelectorAll('.file-input-group').length;
    }

    addFileBtn.addEventListener('click', function () {
        updateFileCount();
        if (fileCount < maxFiles) {
            const fileGroup = document.createElement('div');
            fileGroup.classList.add('file-input-group', 'd-flex', 'mb-2', 'align-items-center', 'gap-2');

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'attachments[]';
            fileInput.accept = 'image/*';
            fileInput.classList.add('form-control');

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.classList.add('btn', 'btn-danger', 'btn-sm');
            removeBtn.innerText = 'Hapus';
            removeBtn.addEventListener('click', function () {
                fileWrapper.removeChild(fileGroup);
                updateFileCount();
            });

            fileGroup.appendChild(fileInput);
            fileGroup.appendChild(removeBtn);
            fileWrapper.appendChild(fileGroup);
        } else {
            Swal.fire({
                        icon: 'warning',
                        title: 'Maksimal 3 File!',
                        confirmButtonText: 'OK'
                    });
        }
    });

    $(document).ready(function () {
        $('#ticket-form').on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `<p>Tiket Berhasil Dikirim </p> <p><strong>Nomor Tiket:</strong> ${response.ticket_number}</p>`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#ticket-form')[0].reset();
                        $('#file-wrapper').empty();
                    });
                },
                error: function (xhr) {
                    let errorMsg = "Terjadi kesalahan saat mengirim.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMsg
                    });
                }
            });
        });
    });
</script>
@endsection
