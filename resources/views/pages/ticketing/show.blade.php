@extends('layouts.app')
@section('title','Ticketing')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Ticketing</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Ticketing</span></div>
    </div>

    <div class="card">
        <div class="card-header">Detail Ticket #{{$ticket->ticket_number}}</div>
        <div class="card-body">
        {{-- <form id="ticket-form" action="{{ route('ticketing.store') }}" method="POST" enctype="multipart/form-data"> --}}
            {{-- @csrf --}}
            <div class="row mb-3">
                <div class="col-1">Judul Masalah</div>
                <div class="col-1">:</div>
                <div class="col-8">{{$ticket->title}}</div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Deskripsi Masalah</div>
                <div class="col-1">:</div>
                <div class="col-8">{{$ticket->description}}</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-1">Priority</div>
                <div class="col-1">:</div>
                <div class="col-8">{{$ticket->priority}}</div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Status</div>
                <div class="col-1">:</div>
                <div class="col-8">{{$ticket->status}}</div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Requester</div>
                <div class="col-1">:</div>
                <div class="col-8">{{$ticket->requester->name}}</div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Assigned To</div>
                <div class="col-1">:</div>
                <div class="col-8">
                    @if($ticket->assigned_employee_id != null)
                        {{ $ticket->assigned->name }}
                    @else 
                        -
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Lampiran Open</div>
                <div class="col-1">:</div>
                <div class="col-8">
                    <ul>
                        @foreach ($ticket->attachmentsOpen as $row)
                            <li>Lampiran {{ $loop->iteration }} <br> <img width="300px" src="{{asset('/storage/'.$row->file_path)}}" href="{{asset('/storage/'.$row->file_path)}}" target="_blank"></li>
                        @endforeach

                    </ul>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-1">Lampiran Solved</div>
                <div class="col-1">:</div>
                <div class="col-8">
                    <ul>
                        @foreach ($ticket->attachmentsClose as $row)
                            <li>Lampiran {{ $loop->iteration }} <br> <img width="300px" src="{{asset('/storage/'.$row->file_path)}}" href="{{asset('/storage/'.$row->file_path)}}" target="_blank"></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            

            {{-- <input type="hiddn" name="requester_id" value="{{ auth()->user()->id }}"> --}}

            
            {{-- Input File Section --}}
            <div class="mb-3">
                <a href="/ticketing" type="button" class="btn btn-primary mt-2"><i class="ri ri-arrow-left-fill"></i> Back</a>
            </div>
        {{-- </form> --}}

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

</script>
@endsection
