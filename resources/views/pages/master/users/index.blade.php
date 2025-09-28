@extends('layouts.app')
@section('title','Master User')

@section('main-content')
<div class="header-breadcrumb">
  <h2 id="page-title">Master User</h2>
  <div class="breadcrumb" id="breadcrumb"> <span>User</span> / Master User </div>
</div>

<div class="card">
  <div class="card-header">Daftar User</div>
  <div class="card-body">
    <div class="mb-3">
      <a class="btn btn-primary text-white" href="{{ route('users.create') }}"><i class="ri ri-add-large-fill"></i> Tambah User</a>
    </div>
    <table id="users-table" class="table table-bordered table-striped w-100">
      <thead>
        <tr>
          <th>No</th>
          <th>NIK</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Department</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="edit-form" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" id="edit-id">
          <div class="col-md-6">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" id="edit-name">
          </div>
          <div class="col-md-6">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" id="edit-phone">
          </div>
          <div class="col-md-6">
            <label>Department</label>
            <select name="department_id" id="edit-department" class="form-control">
              @foreach($depts as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label>Status</label>
            <select name="is_active" id="edit-status" class="form-control">
              <option value="1">Aktif</option>
              <option value="0">Non Aktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('users.data') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nik' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'department' },
            { data: 'is_active', render: d => d == 1 ? 'Aktif':'Non Aktif' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // buka modal edit
    $('#users-table').on('click','.btn-edit',function(){
        const data = $(this).data('json');
        $('#edit-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-phone').val(data.phone);
        $('#edit-department').val(data.department_id);
        $('#edit-status').val(data.is_active);
        $('#editModal').modal('show');
    });

    // submit update
    $('#edit-form').on('submit',function(e){
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = `/master/users/${id}`;
        $.ajax({
            url, method:'POST', data: $(this).serialize(),
            success: res => {
                Swal.fire('Berhasil',res.message,'success');
                $('#editModal').modal('hide');
                table.ajax.reload();
            },
            error: () => Swal.fire('Error','Gagal update user','error')
        });
    });

    // delete
    $('#users-table').on('click','.btn-delete',function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data User ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton:true,
        })
        .then(result=>{
            if(result.isConfirmed){
                $.ajax({
                    url:`/master/users/${id}`, method:'DELETE', data:{_token:'{{ csrf_token() }}'},
                    success:res=>{
                        Swal.fire('Berhasil',res.message,'success');
                        table.ajax.reload();
                    },
                    error:()=>Swal.fire('Error','Gagal hapus user','error')
                });
            }
        })
    });
});
</script>
@endsection
