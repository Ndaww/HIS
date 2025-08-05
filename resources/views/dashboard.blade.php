@extends('layouts.app')
@section('title', 'Dashboard')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Dashboard</h2>
        <div class="breadcrumb" id="breadcrumb">  Dashboard </div>
    </div>

    <div class="container-fluid card p-3">
            <div class="row mb-4">
                <div class="col">
                    <div class="card bg-primary">
                        <div class="card-body">Total Tiket: {{ $total }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-success">
                        <div class="card-body">Status Open: {{ $open }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-warning">
                        <div class="card-body">Prioritas Tinggi: {{ $priority['high'] }}</div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <canvas id="ticketLineChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- <h4>Daftar Tiket Terbaru</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nomor Tiket</th>
                <th>Judul</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Departemen ID</th>
                <th>Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($latestTickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->title }}</td>
                <td>{{ ucfirst($ticket->priority) }}</td>
                <td>{{ ucfirst($ticket->status) }}</td>
                <td>{{ $ticket->department_id }}</td>
                <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table> --}}

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ticketLineChart').getContext('2d');
const ticketLineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dates) !!},
        datasets: [{
            label: 'Jumlah Tiket',
            data: {!! json_encode($counts) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            tension: 0.4,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                precision: 0
            }
        }
    }
});
</script>
@endsection