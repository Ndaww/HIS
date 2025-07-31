@extends('layouts.app')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Tiket</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Ticketing</span> / Daftar Tiket </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Tiket</div>
        <div class="card-body">
            {{-- @dd(auth()->user()->department_id) --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="status">Status</label>
                    <select class="form-control" name="status" id="status">
                        <option value=""> -- Pilih Status -- </option>
                        <option value="open"> Open </option>
                        <option value="in_progress"> In Progress </option>
                        <option value="pending"> Pending </option>
                        <option value="solved"> Solved </option>
                        <option value="closed"> Closed </option>
                    </select>
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
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

    <!-- Modal Delegasi -->
  <div class="modal fade" id="myModalDelegasi" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-lg modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel">Ini Modal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

@endsection


@section('js')
<script>
 $(document).ready(function () {
        let table = $('#tickets-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('list-ticket-dept') }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.status = $('#status').val();
                }
            },
            lengthMenu: [10, 25, 50, 100, -1],
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

        // table.on('draw', function () {
        //     const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        //     popoverTriggerList.forEach(el => {
        //         new bootstrap.Popover(el, { trigger: 'hover', placement: 'top' });
        //     });
        // });


        // show modal view & data saat di klik
        $('#tickets-table').on('click', '.btn-view', function () {
            const id = $(this).data('id');

            $.get('/api/ticket-dept/' + id, function (res) {
                let buttonHtml = '';
                
                if (res.status === 'open') {
                    buttonHtml += `<button type="submit" class="btn-sm btn-primary mt-3 me-1">Progress Ticket <i class="ri ri-progress-1-fill"></i></button>`;
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
                    <form action="/ticketing/progress" method="POST" id="form-progress">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="${res.id}">
                        ${buttonHtml}
                    </form>
                `);

                new bootstrap.Modal(document.getElementById('myModal')).show();
            });
        });
    });

    // button untuk progress ticket
    $(document).on('submit', '#form-progress', function (e) {
        e.preventDefault(); 
        const userName = "{{ auth()->user()->name }}";
        let formData = new FormData(this);
        Swal.fire({
            title: 'Apakah kamu yakin?',
            html: "Tiket ini akan ditandai sebagai <br> <strong> dalam progress oleh : " + userName + "</strong>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Progress Ticket',
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


    // Saat modal Delegasikan diklik
    $('#tickets-table').on('click', '.btn-delegasi', function () {
            const id = $(this).data('id');

            $.get('/api/ticket-dept/' + id, function (res) {
                let buttonHtml = '';
                
                if (res.status === 'open') {
                    buttonHtml += `<button type="submit" class="btn-sm btn-primary mt-3 me-1">Delegasikan <i class="ri ri-plane-line"> </i></button>`;
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

                let employeeOptions = '';

                if (res.employees && res.employees.length > 0) {
                    employeeOptions += `
                        <div class="mt-3">
                            <label for="delegated_employee" class="form-label fw-bold">Delegasikan ke</label>
                            <select class="form-select" name="delegated_employee" id="delegated_employee" required>
                                <option value="" disabled selected>-- Pilih Pegawai --</option>
                    `;

                    res.employees.forEach(function(employee) {
                        employeeOptions += `<option value="${employee.id}">${employee.name}</option>`;
                    });

                    employeeOptions += `</select></div>`;
                }



                $('#myModalLabel').text('Detail Tiket #' + res.ticket_number);
                $('#myModalDelegasi .modal-body').html(`
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
                    <form action="/ticketing/delegasi" method="POST" id="form-delegasi">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="${res.id}">
                        ${employeeOptions}
                        ${buttonHtml}
                    </form>
                `);

                new bootstrap.Modal(document.getElementById('myModalDelegasi')).show();
            });
        });

    
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach(el => {
        new bootstrap.Popover(el, { trigger: 'hover', placement: 'top' });
    });
</script>
@endsection
