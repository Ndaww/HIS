@extends('layouts.app')
@section('title', 'Akses User')

@section('main-content')

<div class="header-breadcrumb">
    <h2 id="page-title">Akses User</h2>
    <div class="breadcrumb"><span>User</span> / Akses</div>
</div>

<div class="card">
    <div class="card-header">Pengaturan Akses User</div>
    <div class="card-body">

        {{-- PILIH USER --}}
        <div class="mb-3">
            <label class="form-label">Pilih User</label>
            <select id="user_id" class="form-control select2">
                <option value="">-- Pilih User --</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ( {{ $u->nik }} )</option>
                @endforeach
            </select>
        </div>
        <hr>
        {{-- LIST MENU --}}
        <div id="menu-container" style="display:none;">
            <div class="mb-2 d-flex justify-content-between">
                <h5>Daftar Menu</h5>

                <div>
                    <button id="checkAll" class="btn btn-sm btn-success">Check All</button>
                    <button id="uncheckAll" class="btn btn-sm btn-danger">Uncheck All</button>
                </div>
            </div>

            <form id="form-akses">
                @csrf
                <table id="menu-table" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Menu</th>
                            <th width="5%">Berhak</th>
                        </tr>
                    </thead>
                </table>

                <button class="btn btn-primary mt-3">Simpan Akses</button>
            </form>
        </div>

    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {

    $('.select2').select2();

    let table;

    // LOAD TABEL SAAT USER DIPILIH
    $('#user_id').on('change', function () {
        let userId = $(this).val();

        if (!userId) {
            $("#menu-container").hide();
            return;
        }

        $("#menu-container").show();

        // Destroy previous table
        if ($.fn.DataTable.isDataTable('#menu-table')) {
            $("#menu-table").DataTable().clear().destroy();
        }

        // INIT DATATABLE
        table = $('#menu-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,    
            lengthChange: false, 
            ajax: "/master/akses/get-menu-dt/" + userId,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
                { data: 'name', name: 'name' },
                {
                    data: 'is_checked',
                    name: 'is_checked',
                    orderable:false,
                    searchable:false,
                    className: 'text-center align-middle',
                    render: function(data, type, row){
                        let checked = data ? 'checked' : '';
                        return `
                            <input type="checkbox" class="menu-check text-c" name="menus[]" value="${row.id}" ${checked}>
                        `;
                    }
                }
            ]
        });

    });

    // CHECK ALL
    $("#checkAll").click(function(){
        $(".menu-table-container input[type='checkbox'], .menu-check").prop('checked', true);
        $(".menu-check").prop('checked', true);
        $("#menu-table input[type='checkbox']").prop('checked', true);
        $("input.menu-check").prop('checked', true);
        $(".menu-check").trigger("change");
        $(".menu-check").prop("checked", true);
        $(".menu-check").each(function(){
            $(this).prop("checked", true);
        });
    });

    // UNCHECK ALL
    $("#uncheckAll").click(function(){
        $(".menu-check").prop('checked', false);
    });

    // SIMPAN DATA
    $("#form-akses").on("submit", function (e) {
        e.preventDefault();

        let userId = $("#user_id").val();
        let data = $(this).serialize();

        $.ajax({
            url: "/master/akses/store/" + userId,
            method: "POST",
            data: data,
            success: function () {
                Swal.fire("Berhasil", "Akses disimpan", "success");
            },
            error: function () {
                Swal.fire("Error", "Terjadi kesalahan", "error");
            }
        });
    });

});
</script>
@endsection
