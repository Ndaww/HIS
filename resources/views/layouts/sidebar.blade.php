<div class="sidebar" id="sidebar">
      <img class="text-center" src="{{asset('/assets/img/logo.png')}}" alt="" width="50%" style="display: block;margin: auto;">
      <hr>
      <ul>
        <li class="menu-item full-click {{request()->is('dashboard*') ? 'active open' : ''}}"><a class="text-decoration-none text-black" href="/dashboard"><i class="ri-sm ri-dashboard-line"></i> Dashboard</a></li>
        @if(auth()->user()->hasMenu(1))
        <li>
          <div class="parent {{request()->is('master*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-book-line"></i> Master <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>
         {{-- @if(auth()->user()->id == 1 || auth()->user()->departmentHeaded) --}}
          <ul class="child-menu" style="{{ request()->is('master*') ? 'display: block;' : '' }}">
            {{-- <li class="full-click {{ request()->is('master/patients*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/patients">Master Pasien</a></li> --}}
            <li class="full-click {{ request()->is('master/rooms*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/rooms">Master Ruangan</a></li>
            <li class="full-click {{ request()->is('master/depts*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/depts">Master Department</a></li>
            <li class="full-click {{ request()->is('master/equipments*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/equipments">Master Equipment</a></li>
            <li class="full-click {{ request()->is('master/users*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/users">Master User</a></li>
            <li class="full-click {{ request()->is('master/akses*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/akses">Master Akses</a></li>
            <li class="full-click {{ request()->is('master/technicians*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/technicians">Master Teknisi</a></li>
            <li class="full-click {{ request()->is('master/specializations*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/specializations">Master Spesialisasi</a></li>
            <li class="full-click {{ request()->is('master/task*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/master/task">Master Task (Shift)</a></li>
          </ul>
        </li>
        @endif
        <li>
          {{-- <div class="parent {{request()->is('ticketing*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-ticket-line"></i> Ticketing <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('ticketing*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('ticketing/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/create">Buat Tiket</a></li>
            <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li>
            <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li>
          </ul>
        </li> --}}

        @if(auth()->user()->hasMenu(2))
        <li>
          <div class="parent {{request()->is('ticket/v2*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-ticket-line"></i> Ticketing V2 <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('ticket/v2*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('ticket/v2/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticket/v2/create">Buat Tiket</a></li>
            <li class="full-click {{ request()->is('ticket/v2') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticket/v2">Pengajuan Saya</a></li>
            <li class="full-click {{ request()->is('ticket/v2/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticket/v2/dept">Semua Tiket</a></li>
          </ul>
        </li>
        @endif
        {{-- <li>
          <div class="parent {{request()->is('preventive*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-task-line"></i> Preventive <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('preventive*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('preventive/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/create">Buat Jadwal</a></li>
            <li class="full-click {{ request()->is('preventive/task') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/task">Tugas Saya</a></li>
            <li class="full-click {{ request()->is('preventive/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/history">History Tugas Saya</a></li>
          </ul>
        </li> --}}

        {{-- @if(auth()->user()->id == 1 || auth()->user()->department_id == 4 ) --}}
        @if(auth()->user()->hasMenu(3))
        {{-- <li>
          <div class="parent {{request()->is('preventive-task/*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-task-line"></i> Preventive <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>


          <ul class="child-menu" style="{{ request()->is('preventive-task/equipment/*') ? 'display: block;' : '' }}">
            {{-- equipment 
            <div class="parent {{request()->is('preventive-task/equipment*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
                <i class="ri-sm ri-task-line"></i> Equipment <span class="arrow"><i class="ri ri-play-fill"></i></span>
            </div>
            <ul class="child-menu" style="{{ request()->is('preventive-task/equipment/*') ? 'display: block;' : '' }}">
                @foreach ($equipmentSidebar as $item)
                    <li class="full-click {{ request()->is('preventive-task/equipment/'.$item->id.'*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive-task/equipment/{{$item->id}}/form">{{$item->name}}</a></li>
                @endforeach
            </ul>

            {{-- preventive 
            @if (auth()->user()->id == 1 || auth()->user()->departmentHeaded)
            <li class="full-click {{ request()->is('preventive/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/create">Buat Jadwal</a></li>
            @endif
            <li class="full-click {{ request()->is('preventive/task') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/task">Tugas Saya</a></li>
            <li class="full-click {{ request()->is('preventive/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/history">History Tugas Saya</a></li>
          </ul>

        </li> --}}

        <li>
          <div class="parent {{ request()->is('preventive/shift*') || request()->is('preventive/v2*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
            <i class="ri-sm ri-task-line"></i> Preventive V2 <span class="arrow"><i class="ri ri-play-fill"></i></span>
          </div>


          <ul class="child-menu" style="{{ request()->is('preventive/shift*') || request()->is('preventive/v2*') ? 'display: block;' : '' }}">
            {{-- equipment --}}
            <div class="parent {{request()->is('preventive/v2/equipment*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
                <i class="ri-sm ri-task-line"></i> Equipment <span class="arrow"><i class="ri ri-play-fill"></i></span>
            </div>
            <ul class="child-menu" style="{{ request()->is('preventive/v2/equipment/*') ? 'display: block;' : '' }}">
                @foreach ($equipmentSidebar as $item)
                    <li class="full-click {{ request()->is('preventive/v2/equipment/'.$item->id.'*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive-task/equipment/{{$item->id}}/form">{{$item->name}}</a></li>
                @endforeach
            </ul>

            {{-- Rutin Per Shift --}}
            <div class="parent {{ request()->is('preventive/shift*') ? 'active open' : '' }}" onclick="toggleChildMenu(this)">
                <i class="ri-sm ri-task-line"></i> Rutin Per Shift <span class="arrow"><i class="ri ri-play-fill"></i></span>
            </div>
            <ul class="child-menu" style="{{ request()->is('preventive/shift*') ? 'display: block;' : '' }}">
                {{-- preventive --}}
              <li class="full-click {{ request()->is('preventive/shift/dashboard') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/shift/dashboard">Dashboard</a></li>
              @if (auth()->user()->id == 1 || auth()->user()->departmentHeaded)
              <li class="full-click {{ request()->is('preventive/shift/schedule') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/shift/schedule">Buat Jadwal</a></li>
              @endif
              {{-- <li class="full-click {{ request()->is('preventive/v2/target/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/target/create">Buat Jadwal</a></li> --}}
              <li class="full-click {{ request()->is('preventive/shift/my-tasks') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/shift/my-tasks">Tugas Saya</a></li>
              <li class="full-click {{ request()->is('preventive/shift/my-tasks/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/shift/my-tasks/history">History Tugas Saya</a></li>
              {{-- <li class="full-click {{ request()->is('preventive/v2/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/history">History Tugas Saya</a></li> --}}
            </ul>

            {{-- Spesialist --}}
            <div class="parent {{request()->is('preventive/v2*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
                <i class="ri-sm ri-task-line"></i> Spesialist <span class="arrow"><i class="ri ri-play-fill"></i></span>
            </div>
            <ul class="child-menu" style="{{ request()->is('preventive/v2*') ? 'display: block;' : '' }}">
                {{-- preventive --}}
              <li class="full-click {{ request()->is('preventive/v2/dashboard') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/dashboard">Dashboard</a></li>
              @if (auth()->user()->id == 1 || auth()->user()->departmentHeaded)
              <li class="full-click {{ request()->is('preventive/v2/target/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/target/create">Buat Jadwal</a></li>
              @endif
              <li class="full-click {{ request()->is('preventive/v2/task') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/task">Tugas Saya</a></li>
              <li class="full-click {{ request()->is('preventive/v2/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/v2/history">History Tugas Saya</a></li>
            </ul>

          </ul>

        </li>
        {{-- <li>
          <div class="parent {{request()->is('pks*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-shake-hands-line "></i> PKS <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('pks*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('pks/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/create">Buat Pengajuan</a></li>
            <li class="full-click {{ request()->is('pks/pengajuan-saya') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/pengajuan-saya">Pengajuan Saya</a></li>
            <li class="full-click {{ request()->is('pks/verify') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/verify">Data PKS (Legal)</a></li>
            <li class="full-click {{ request()->is('pks/approval') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/approval">Approval Direksi</a></li>
          </ul>
        </li>
        <li>
          <div class="parent {{request()->is('kamar-kosong*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-hotel-bed-line"></i> Kamar Kosong <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('kamar-kosong*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('kamar-kosong/bookings*') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/kamar-kosong/bookings">Booking Kamar</a></li>
            <li class="full-click {{ request()->is('kamar-kosong/validasi') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/kamar-kosong/validasi">Validasi GA</a></li>
            <li class="full-click {{ request()->is('kamar-kosong/konfirmasi') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/kamar-kosong/konfirmasi">Konfirmasi Perawat</a></li>
          </ul>
        </li> --}}
        @endif

        @if(auth()->user()->hasMenu(4))
        <li>
          <div class="parent {{request()->is('reports*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-folder-5-line"></i> Laporan <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('reports*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('reports/ticket') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/ticket"> <i class="ri-sm ri-ticket-fill"></i> Laporan Ticketing</a></li>
            <li class="full-click {{ request()->is('reports/preventive') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/preventive"> <i class="ri-sm ri-task-fill"></i> Laporan Preventive</a></li>
            <li class="full-click {{ request()->is('reports/preventive/v2') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/preventive/v2"> <i class="ri-sm ri-task-fill"></i> Laporan Preventive V2</a></li>
            {{-- <li class="full-click {{ request()->is('reports/pks') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/pks"> <i class="ri-sm ri-shake-hands-line"></i> Laporan PKS</a></li> --}}
            {{-- <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li> --}}
            {{-- <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li> --}}
          </ul>
        </li>
        @endif

        {{-- @if (auth()->user()->id == 1) --}}
        @if(auth()->user()->hasMenu(5))
            <li>
          <div class="parent {{request()->is('zawa*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-whatsapp-fill"></i> ZAWA <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('zawa*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('zawa/create-session') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/zawa/create-session"> <i class="ri-sm ri-ticket-fill"></i> Buat Sesi</a></li>
            <li class="full-click {{ request()->is('zawa/check-status') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/zawa/check-status"> <i class="ri-sm ri-task-fill"></i> Cek Status</a></li>
          </ul>
        </li>
        @endif
        
      </ul>
    </div>
