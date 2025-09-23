@extends('layouts.app')

@section('title', 'Preventive - Form Tindakan Preventif')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Form Tindakan Preventif</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Form Tindakan Preventif</div>
    </div>

    <div class="card">
        <div class="card-header">
            Form Tindakan Preventif Equipment
        </div>
        <div class="card-body">
            <h4 class="mb-3">Form Tindakan Preventif - {{ $eqType->name }}</h4>

            <form method="POST" action="{{ route('preventive-task-equipment.store-task', $eqType->id) }}">
                @csrf
                <input type="hidden" name="room_id" id="hidden_room_id">
                <input type="hidden" name="equipment_type_id" id="hidden_equipment_type_id">
                <input type="hidden" name="equipment_id" id="hidden_equipment_id">

                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label" for="equipment_type_name">Tipe Equipment</label>
                        <input class="form-control" type="text" value="{{ $eqType->name }}" name="equipment_type_name" readonly>
                        <input type="hidden" name="equipment_type_id" value="{{ $eqType->id }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="room_id">Ruangan</label>
                        <select class="form-select" name="room_id" id="room_id">
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $row)
                                <option value="{{ $row->room_id }}">{{ $row->room->floor }} - {{ $row->room->name }} - {{ $row->room->class }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4" id="equipment-select-container" style="display: none;">
                        <label class="form-label" for="equipment_id">Equipment</label>
                        <select class="form-select" name="equipment_id" id="equipment_id">
                            <option value="">-- Pilih Equipment --</option>
                        </select>
                    </div>
                </div>

                <div id="preventive-tasks-container" style="display: none;">
                    </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" id="lock-form-btn" class="btn btn-secondary me-2" style="display: none;">Kunci & Lanjutkan</button>
                    <button type="submit" class="btn btn-primary ms-auto" style="display: none;" id="submit-btn">Simpan</button>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const roomSelect = document.getElementById('room_id');
    const equipmentContainer = document.getElementById('equipment-select-container');
    const equipmentSelect = document.getElementById('equipment_id');
    const preventiveTasksContainer = document.getElementById('preventive-tasks-container');
    const lockFormBtn = document.getElementById('lock-form-btn');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.querySelector('form');

    roomSelect.addEventListener('change', function() {
        const roomId = this.value;
        const eqTypeId = '{{ $eqType->id }}';

        if (!roomId) {
            equipmentContainer.style.display = 'none';
            preventiveTasksContainer.style.display = 'none';
            lockFormBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            return;
        }

        equipmentContainer.style.display = 'block';
        equipmentSelect.innerHTML = '<option value="">-- Pilih Equipment --</option>';

        preventiveTasksContainer.style.display = 'none';
        lockFormBtn.style.display = 'none';
        submitBtn.style.display = 'none';

        fetch(`/get-equipments/${eqTypeId}/${roomId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(equipment => {
                    const option = document.createElement('option');
                    option.value = equipment.id;
                    option.textContent = `${equipment.room.name} - ${equipment.name}`;
                    equipmentSelect.appendChild(option);
                });
            });
    });

    equipmentSelect.addEventListener('change', function() {
        const equipmentId = this.value;

        if (!equipmentId) {
            lockFormBtn.style.display = 'none';
            preventiveTasksContainer.style.display = 'none';
            submitBtn.style.display = 'none';
            return;
        }

        lockFormBtn.style.display = 'block';

        // cek preventive status
        fetch(`/check-preventive-status/${equipmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'done') {
                    // Jika status terakhir 'done', tampilkan SweetAlert informasi
                    Swal.fire({
                        icon: 'info',
                        title: 'Status Preventif',
                        text: data.message,
                    });
                } else if (data.status == 'pp') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Status Preventif',
                        text: data.message,
                    }).then((result) => {
                            equipmentSelect.value = '';
                            lockFormBtn.style.display = 'none';
                    });
                } else if (data.status === 'new') {
                    // Jika belum ada atau status bukan 'done', tampilkan SweetAlert konfirmasi
                    Swal.fire({
                        icon: 'question',
                        title: 'Jadwal Preventif',
                        text: data.message,
                        showCancelButton: true,
                        confirmButtonText: 'Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            equipmentSelect.value = '';
                            lockFormBtn.style.display = 'none';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error checking preventive status:', error);
            });
    });

    // Kunci form
    lockFormBtn.addEventListener('click', function() {
        const eqTypeId = '{{ $eqType->id }}';
        const selectedRoomId = roomSelect.value;
        const selectedEquipmentId = equipmentSelect.value;
        const selectedEqTypeId = '{{ $eqType->id }}';

        // Isi hidden input dengan nilai yang dipilih
        document.getElementById('hidden_room_id').value = selectedRoomId;
        document.getElementById('hidden_equipment_id').value = selectedEquipmentId;
        document.getElementById('hidden_equipment_type_id').value = selectedEqTypeId;

        roomSelect.setAttribute('disabled', true);
        equipmentSelect.setAttribute('disabled', true);

        lockFormBtn.style.display = 'none';
        submitBtn.style.display = 'block';

        preventiveTasksContainer.style.display = 'block';

        // Ambil list tasks
        fetch(`/get-preventive-tasks/${eqTypeId}`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.forEach(task => {
                    html += `
                    <div class="card mb-2">
                        <div class ="card-header">
                            ${task.name}
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                        <input class="" type="hidden" name="preventive_task_ids[]" value="${task.id}" id="task_${task.id}">
                                </div>
                                <div class="col-12">
                                    <label for="task_notes_${task.id}" class="form-label">Catatan</label>
                                    <textarea class="form-control" name="task_notes[${task.id}]" id="task_notes_${task.id}" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                });

                const selectedEquipmentId = equipmentSelect.value;
                html += `<input type="hidden" name="equipment_id" value="${selectedEquipmentId}">`;

                preventiveTasksContainer.innerHTML = html;

                Swal.fire({
                    icon: 'success',
                    title: 'Form Siap Diisi',
                    text: 'Silakan isi checklist dan catatan preventif.',
                });
            })
            .catch(error => {
                console.error('Error fetching preventive tasks:', error);
            });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}'
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}'
        });
    @endif
</script>
@endsection
