@extends('layouts.app')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Tiket</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Ticketing</span> / Daftar Tiket Departement {{ auth()->user()->dept->name }} </div>
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
                    <label for="assigned_employee_id">Ditugaskan ke :</label>
                    <select class="form-control" name="assigned_employee_id" id="assigned_employee_id">
                        <option value=""> -- Pilih Pekerja -- </option>
                        <option value="">Belum Ditugaskan</option>
                        <option class="text-capitalize" value="{{auth()->user()->id}}">{{ auth()->user()->name }} ( Saya ) </option>
                        @foreach ($assigneds as $row)
                            <option class="text-capitalize" value="{{$row->id}}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 align-self-end mt-3">
                    <button id="filter" class="btn btn-primary"> <i class="ri ri-filter-line"></i>Filter</button>
                    <button id="reset" class="btn btn-secondary"><i class="ri ri-refresh-line"></i> Reset</button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">Keterangan Tombol</div>
                <div class="col-md-1">:</div>
                <div class="col">
                    <button href="javascript:void(0)" class="btn btn-sm btn-info btn-view me-1" title="Lihat">
                            <i class="ri-sm ri-eye-line"></i> Lihat / Progress
                    </button>
                    <button href="javascript:void(0)"
                            class="btn btn-sm btn-danger btn-pending"
                            title="Pending">
                            <i class="ri-sm ri-compass-4-line"></i> Pending
                        </button> <button href="javascript:void(0)"
                            class="btn btn-sm btn-success btn-solve"
                            title="Solved">
                            <i class="ri-sm ri-check-line"></i> Solve
                        </button>
                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-warning btn-eskalasi"
                            title="Eskalasi">
                            <i class="ri-sm ri-exchange-2-line"></i> Eskalasi
                        </button>
                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-primary btn-delegasi"
                            title="Delegasikan">
                            <i class="ri-sm ri-send-plane-line"></i> Delegasi
                        </button>
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

    {{-- modal view --}}
    <div class="modal fade" id="myModalView" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalViewLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
            </div>
        </div>
    </div>

    {{-- modal delegasi --}}
    <div class="modal fade" id="myModalDelegasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalDelegasiLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
            </div>
        </div>
    </div>

    {{-- modal Pending --}}
    <div class="modal fade" id="myModalPending" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalPendingLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
            </div>
        </div>
    </div>

    {{-- modal Solve --}}
    <div class="modal fade" id="myModalSolve" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalSolveLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
            </div>
        </div>
    </div>

    {{-- modal Eskalasi --}}
    <div class="modal fade" id="myModalEskalasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalEskalasiLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

            </div>
            </div>
        </div>
    </div>

@endsection


@section('js')
<script>
// Fungsi untuk mengambil dan memperbarui jumlah tiket
function updateTicketCounts() {
    fetch('/ticket/v2/ticket-depts-counts')
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
updateTicketCounts();
 $(document).ready(function () {
        let table = $('#tickets-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('list-ticket-dept-v2') }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    // d.status = $('#status').val();
                    d.status = $('#ticketStatusTabs .nav-link.active').data('status');
                    d.assigned_employee_id = $('#assigned_employee_id').val();
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
                { data: 'requester_name', name: 'requester_id'},
                { data: 'dept_name', name: 'department_id' },
                { data: 'assigned_name', name: 'assigned_employee_id'},
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

        function generateAttachments(attachments, label) {
            if (!attachments || attachments.length === 0) return '';

            let html = `<div class="mt-3"><strong>Lampiran (${label}):</strong><div class="row g-2 mt-1">`;
            attachments.forEach(item => {
                html += `
                    <div class="col-4">
                        <a href="${item.file_path}" target="_blank">
                            <img src="${item.file_path}" class="img-fluid rounded border" style="max-height: 100px; object-fit: cover;" />
                        </a>
                    </div>
                `;
            });
            html += `</div></div>`;
            return html;
        }

        function generateTicketDetails(res) {
            const fields = [
                ['Nomor Tiket', res.ticket_number],
                ['Judul', res.title],
                ['Deskripsi', res.description],
                ['Status', res.status],
                ['Prioritas', res.priority],
                ['Requester', res.requester_name],
                ['Department', res.department_name],
                ['Dibuat Pada', res.created_at],
            ];

            return fields.map(([label, value]) => `
                <div class="row mb-2">
                    <div class="col-4 fw-bold">${label}</div>
                    <div class="col-8">${value}</div>
                </div>
            `).join('');
        }

        function generateEmployeeSelect(employees) {
            if (!employees || employees.length === 0) return '';

            let html = `
                <div class="mt-3">
                    <label for="delegated_employee" class="form-label fw-bold">Delegasikan ke</label>
                    <select class="form-select" name="delegated_employee" id="delegated_employee" required>
                        <option value="" disabled selected>-- Pilih Pegawai --</option>
            `;

            employees.forEach(emp => {
                html += `<option value="${emp.id}">${emp.name}</option>`;
            });

            html += `</select></div>`;
            return html;
        }

        function generateEscalatedEmployeeSelect(teams) {
            if (!teams || teams.length === 0) return '';

            let html = `
                <div class="mt-3">
                    <label for="escalated_reason" class="form-label fw-bold">Alasan Eskalasi</label>
                    <textarea class="form-control" name="escalated_reason" id="escalated_reason" cols="30" rows="5" required></textarea>
                <div class="mt-3">
                    <label for="escalated_employee" class="form-label fw-bold">Eskalasi ke</label>
                    <select class="form-select" name="escalated_employee" id="escalated_employee" required>
                        <option value="" disabled selected>-- Pilih Pegawai --</option>
            `;

            teams.forEach(team => {
                html += `<option value="${team.id}">${team.name}</option>`;
            });

            html += `</select></div>`;
            return html;
        }

        function renderTicketModal(res, mode = 'view') {
            const modalId = 'myModal' + (mode.charAt(0).toUpperCase() + mode.slice(1)); // e.g. myModalSolve
            const formAction = {
                view: '/ticketing/progress',
                delegasi: '/ticketing/delegasi',
                pending: '/ticketing/pending',
                solve: '/ticketing/solve',
                eskalasi: '/ticketing/eskalasi'
            }[mode] || '';

            const buttonLabel = {
                view: '',
                delegasi: 'Delegasikan',
                pending: 'Pending Ticket',
                solve: 'Selesaikan',
                eskalasi: 'Eskalasi Ticket'
            }[mode] || '';

            const buttonIcon = {
                view: '',
                delegasi: 'ri-send-plane-line',
                pending: 'ri-compass-4-line',
                solve: 'ri-check-line',
                eskalasi: 'ri-upload-2-line'
            }[mode] || '';

            let buttonHtml = '';
            if (mode !== 'view' && res.status !== 'closed') {
                buttonHtml = `<button type="submit" class="btn-sm btn-primary mt-3 me-1">${buttonLabel} <i class="ri ${buttonIcon}"></i></button>`;
            }

            if (res.status === 'open') {
                buttonHtml = `<button type="submit" class="btn-sm btn-primary mt-3 me-1">Progress Ticket <i class="ri ri-progress-1-fill"></i></button>`;
            }

            const attachmentOpenHTML = generateAttachments(res.attachments_open, 'Open');
            const attachmentCloseHTML = generateAttachments(res.attachments_close, 'Close');

            let dynamicFields = '';
            if (mode === 'delegasi') {
                dynamicFields = generateEmployeeSelect(res.employees);
            } else if (mode === 'pending') {
                dynamicFields = `
                    <div class="mt-3">
                        <label for="reason" class="form-label fw-bold">Alasan Pending</label>
                        <textarea class="form-control" name="reason" id="reason" required></textarea>
                    </div>`;
            } else if (mode === 'solve') {
                dynamicFields = `
                    <div class="mt-3">
                        <label for="keterangan" class="form-label fw-bold">Keterangan Penyelesaian</label>
                        <textarea class="form-control" name="keterangan" id="keterangan"></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">Lampiran (Opsional) Max 2MB Per File </label>
                        <div id="file-wrapper-solve">
                            <!-- Tempat input file akan ditambahkan -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-file-input-solve">
                            Tambah Lampiran
                        </button>
                    </div>
                `;

            } else if (mode === 'eskalasi') {
                dynamicFields = generateEscalatedEmployeeSelect(res.teams);
            }

            $(`#${modalId}Label`).text('Detail Tiket #' + res.ticket_number);
            $(`#${modalId} .modal-body`).html(`
                ${generateTicketDetails(res)}
                ${attachmentOpenHTML}
                ${attachmentCloseHTML}
                <form action="${formAction}" method="POST" enctype="multipart/form-data" id="form-${mode}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="${res.id}">
                    ${dynamicFields}
                    ${buttonHtml}
                </form>
            `);

            new bootstrap.Modal(document.getElementById(modalId)).show();
        }


        // perbuttonan
        $('#tickets-table').on('click', '.btn-view, .btn-delegasi, .btn-pending, .btn-solve, .btn-eskalasi', function () {
            const id = $(this).data('id');
            const classes = $(this).attr('class');
            let mode = '';

            if (classes.includes('btn-view')) mode = 'view';
            else if (classes.includes('btn-delegasi')) mode = 'delegasi';
            else if (classes.includes('btn-pending')) mode = 'pending';
            else if (classes.includes('btn-solve')) mode = 'solve';
            else if (classes.includes('btn-eskalasi')) mode = 'eskalasi';

            $.get('/api/ticket-dept/' + id, function (res) {
                renderTicketModal(res, mode);
            });
        });


        $(document).on('submit', 'form[id^="form-"]', function (e) {
            e.preventDefault();

            const form = $(this);
            const formId = form.attr('id');
            const userName = "{{ auth()->user()->name }}";

            // Judul dan teks konfirmasi per tipe form
            const config = {
                'form-view': {
                    title: 'Apakah kamu yakin?',
                    html: `Tiket ini akan ditandai sebagai <br><strong>dalam progress oleh: ${userName}</strong>`,
                    confirmText: 'Ya, Progress Ticket',
                },
                'form-delegasi': {
                    title: 'Delegasi Tiket',
                    html: 'Tiket akan didelegasikan ke karyawan terpilih. Lanjutkan?',
                    confirmText: 'Ya, Delegasikan',
                },
                'form-pending': {
                    title: 'Pendingkan Tiket?',
                    html: 'Tiket ini akan ditandai sebagai pending.<br>Pastikan alasan sudah diisi.',
                    confirmText: 'Ya, Pendingkan',
                },
                'form-solve': {
                    title: 'Selesaikan Tiket?',
                    html: 'Tiket ini akan ditandai sebagai selesai.<br>Pastikan keterangan dan lampiran (jika ada) sudah sesuai.',
                    confirmText: 'Ya, Selesaikan',
                },
                'form-eskalasi': {
                    title: 'Eskalasi Tiket',
                    html: 'Tiket ini akan dieskalasi ke karyawan atau pihak lain.<br>Pastikan pilihan sudah tepat.',
                    confirmText: 'Ya, Eskalasi',
                }
            };

            const alertConfig = config[formId] || {
                title: 'Konfirmasi Aksi',
                html: 'Lanjutkan proses?',
                confirmText: 'Ya',
            };

            const formData = new FormData(this);

            Swal.fire({
                title: alertConfig.title,
                html: alertConfig.html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: alertConfig.confirmText,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
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
                                html: `<p>${response.message}</p><p><strong>Nomor Tiket:</strong> ${response.ticket_number}</p>`,
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
    });


    // button tambah lampiran solve
    let fileInputSolveCount = 0;
    $(document).on('click', '#add-file-input-solve', function () {
        if (fileInputSolveCount >= 3) return;

        fileInputSolveCount++;
        $('#file-wrapper-solve').append(`
            <div class="input-group mb-2">
                <input type="file" name="attachments[]" class="form-control" accept="image/*" />
                <button class="btn btn-outline-danger remove-file-input-solve" type="button">Hapus</button>
            </div>
        `);
    });

    // Hapus file input solve
    $(document).on('click', '.remove-file-input-solve', function () {
        $(this).closest('.input-group').remove();
        fileInputSolveCount--;
    });

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach(el => {
        new bootstrap.Popover(el, { trigger: 'hover', placement: 'top' });
    });
</script>
@endsection
