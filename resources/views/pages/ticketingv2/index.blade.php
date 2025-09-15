@extends('layouts.app')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Tiket</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Ticketing</span> / Daftar Pengajuan Tiket Oleh Saya </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <style>
                .nav-buttons .nav-link {
                    background-color: #f8f9fa; /* Latar belakang abu-abu terang */
                    color: #495057; /* Warna teks default */
                    border: 1px solid #dee2e6; /* Border tipis */
                    border-radius: 0.25rem; /* Sudut membulat */
                    margin-right: 0.5rem; /* Jarak antar tombol */
                    transition: all 0.2s ease-in-out;
                }
                .nav-buttons .nav-link.active {
                    background-color: #2E7D32; /* Latar belakang biru saat aktif */
                    color: #fff; /* Teks putih saat aktif */
                    border-color: #2E7D32;
                }
                .nav-buttons .nav-link:hover:not(.active) {
                    background-color: #e9ecef; /* Latar belakang saat di-hover */
                }
            </style>
                <ul class="nav nav-buttons" id="ticketStatusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="open-tab" data-status="open" type="button" role="tab">
                                Open <span class="badge rounded-pill bg-danger pb-1" id="open-count"></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inprogress-tab" data-status="in_progress" type="button" role="tab">
                                In Progress <span class="badge rounded-pill bg-secondary pb-1" id="inprogress-count"></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-status="pending" type="button" role="tab">
                                Pending <span class="badge rounded-pill bg-warning text-dark pb-1" id="pending-count"></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="solved-tab" data-status="solved" type="button" role="tab">
                                Solved <span class="badge rounded-pill bg-success pb-1" id="solved-count"></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="closed-tab" data-status="closed" type="button" role="tab">
                                Closed <span class="badge rounded-pill bg-info pb-1" id="closed-count"></span>
                            </button>
                        </li>
                    </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
                <div class="col-md-3 align-self-end">
                    <button id="filter" class="btn btn-primary"> <i class="ri ri-filter-line"></i>Filter</button>
                    <button id="reset" class="btn btn-secondary"><i class="ri ri-refresh-line"></i> Reset</button>
                </div>
            </div>
            <table id="tickets-table" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Tiket</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Assigned</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Dibuat</th>
                        <th>Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal View -->
  <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-lg modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Ini Modal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <form action="/ticketing" method="POST">
            @method('put')
            {{-- <div class="row mb-3">
                <div class="col-6">
                    <label for=""></label>
                </div>
            </div> --}}

            </form>
          Ini isi dari modal yang muncul saat tombol diklik.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  </div>
@endsection


@section('js')
<script>
// Fungsi untuk mengambil dan memperbarui jumlah tiket
function updateTicketCounts() {
    fetch('/ticket/v2/ticket-counts')
        .then(response => response.json())
        .then(counts => {
            // Perbarui badge untuk setiap status
            document.getElementById('open-count').innerText = counts.open > 0 ? counts.open : '';
            document.getElementById('inprogress-count').innerText = counts.in_progress > 0 ? counts.in_progress : '';
            document.getElementById('pending-count').innerText = counts.pending > 0 ? counts.pending : '';
            document.getElementById('solved-count').innerText = counts.solved > 0 ? counts.solved : '';
            document.getElementById('closed-count').innerText = counts.closed > 0 ? counts.closed : '';
        })
        .catch(error => {
            console.error('Error fetching ticket counts:', error);
        });
}

 $(document).ready(function () {
        let table = $('#tickets-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('list-ticket') }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    // d.status = $('#status').val();
                    d.status = $('#ticketStatusTabs .nav-link.active').data('status');
                }
            },
            lengthMenu: [10, 25, 50, 100],
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
                "<'row mb-3'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success',
                    title: 'Daftar Ticketing'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: 'Daftar Ticketing'
                }
            ],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'ticket_number' },
                { data: 'title' },
                { data: 'description' },
                { data: 'requester_name', name: 'requester.name' },
                { data: 'dept_name', name: 'dept.name' },
                { data: 'assigned_name', name: 'assigned.name' },
                { data: 'status' },
                { data: 'priority' },
                { data: 'created_at' },
                { data: 'updated_at' },
                { data: 'action', orderable: false, searchable: false },
            ]
        });

         // Event listener untuk klik pada tab status
        $('#ticketStatusTabs .nav-link').on('click', function() {
            // Hapus kelas 'active' dari semua tab
            $('#ticketStatusTabs .nav-link').removeClass('active');

            // Tambahkan kelas 'active' ke tab yang diklik
            $(this).addClass('active');

            // Reload DataTable untuk memfilter berdasarkan status baru
            table.ajax.reload();
            updateTicketCounts();
        });

        $('#filter').on('click', function () {
            // console.log('Filter:', $('#start_date').val(), $('#end_date').val()); // ✅ DEBUG

            table.ajax.reload();
        });

        $('#reset').on('click', function () {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#status').val('');
            table.ajax.reload();
        });

        // show modal & data saat di klik
        $('#tickets-table').on('click', '.btn-view', function () {
            const id = $(this).data('id');

            $.get('/api/ticket-dept/' + id, function (res) {
                let buttonHtml = '';
                if (res.status === 'closed') {
                    buttonHtml = `<button type="button" class="btn btn-outline-success mt-3" disabled>Ticket Sudah Selesai <i class="ri ri-check-double-fill"></i></button>`;
                } else {
                    buttonHtml = `<button type="submit" class="btn btn-success mt-3 ">Close Ticket</button>`;
                }

                // Lampiran type: open
                let attachmentOpenHTML = '';
                if (res.attachments_open && res.attachments_open.length > 0) {
                    attachmentOpenHTML += `<div class="mt-3"><strong>Lampiran (Open):</strong><div class="row g-2 mt-1">`;

                    for (let i = 0; i < res.attachments_open.length; i++) {
                        const item = res.attachments_open[i];

                        attachmentOpenHTML += `
                            <div class="col-4">
                                <a href="${item.file_path}" target="_blank">
                                    <img src="${item.file_path}" class="img-fluid rounded border" style="max-height: 100px; object-fit: cover;" />
                                </a>
                            </div>
                        `;
                    }

                    attachmentOpenHTML += `</div></div>`;
                }

                // Lampiran type: close
                let attachmentCloseHTML = '';
                if (res.attachments_close && res.attachments_close.length > 0) {
                    attachmentCloseHTML += `<div class="mt-3"><strong>Lampiran (Close):</strong><div class="row g-2 mt-1">`;

                    for (let i = 0; i < res.attachments_close.length; i++) {
                        const item = res.attachments_close[i];

                        attachmentCloseHTML += `
                            <div class="col-4">
                                <a href="${item.file_path}" target="_blank">
                                    <img src="${item.file_path}" class="img-fluid rounded border" style="max-height: 100px; object-fit: cover;" />
                                </a>
                            </div>
                        `;
                    }

                    attachmentCloseHTML += `</div></div>`;
                }


                $('#myModalLabel').text('Detail Tiket #' + res.ticket_number);
                $('#myModal .modal-body').html(`
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Nomor Tiket</div>
                        <div class="col-8">${res.ticket_number}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Judul</div>
                        <div class="col-8">${res.title}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Deskripsi</div>
                        <div class="col-8">${res.description}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Status</div>
                        <div class="col-8">${res.status}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Prioritas</div>
                        <div class="col-8">${res.priority}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Requester</div>
                        <div class="col-8">${res.requester_name}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Department</div>
                        <div class="col-8">${res.department_name}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Dibuat Pada</div>
                        <div class="col-8">${res.created_at}</div>
                    </div>
                    ${attachmentOpenHTML}
                    ${attachmentCloseHTML}
                    <form action="/ticketing/selesai" method="POST" id="form-selesai">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="${res.id}">
                        ${buttonHtml}
                    </form>
                `);

                new bootstrap.Modal(document.getElementById('myModal')).show();
            });
        });
    });

    // button untuk lihat dan close ticket
    $(document).on('submit', '#form-selesai', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "Tiket ini akan ditandai sebagai selesai.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, tandai selesai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // e.target.submit();
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
                            html: `<p>${response.message}</p> <p><strong>Nomor Tiket:</strong> ${response.ticket_number}</p>`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
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
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });


    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach(el => {
        new bootstrap.Popover(el, { trigger: 'hover', placement: 'top' });
    });
    updateTicketCounts();
</script>
@endsection
