@extends('layouts.app')
@section('title','Tambah User')

@section('main-content')
<div class="header-breadcrumb">
  <h2 id="page-title">Master User</h2>
  <div class="breadcrumb" id="breadcrumb"> <span>User</span> / Tambah User </div>
</div>
<div class="card">
  <div class="card-header">Tambah User</div>
  <div class="card-body">
    <form method="POST" action="{{ route('users.store') }}">
      @csrf
      <div class="row g-3">
        <div class="col-md-4">
          <label>NIK</label>
          <input type="text" name="nik" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>Nama</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>Email</label>
          <input type="email" class="form-control" placeholder="example@gmail.com" name="email" required>
        </div>
        <div class="col-md-4">
          <label>Phone</label>
          <div class="input-group">
                <span class="input-group-text" id="basic-addon1">+62</span>
                <input type="text" class="form-control" placeholder="8123456789" name="phone" required>
            </div>
        </div>
        <div class="col-md-4">
          <label>Department</label>
          <select name="department_id" class="form-control" required>
            @foreach($departments as $d)
              <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</div>
@endsection
