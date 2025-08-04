<div class="sidebar" id="sidebar">
      <h2>Menu</h2>
      <ul>
        <li class="menu-item"><i class="ri-sm ri-dashboard-line"></i> Dashboard</li>
        <li>
          <div class="parent {{request()->is('ticketing*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-ticket-line"></i> Ticketing <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('ticketing*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('ticketing/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/create">Buat Tiket</a></li>
            <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li>
            <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li>
          </ul>
        </li>
        <li>
          <div class="parent {{request()->is('preventive*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-task-line"></i> Preventive <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('preventive*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('preventive/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/create">Buat Jadwal</a></li>
            <li class="full-click {{ request()->is('preventive/task') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/task">Tugas Saya</a></li>
            <li class="full-click {{ request()->is('preventive/history') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/preventive/history">History Tugas Saya</a></li>
          </ul>
        </li>
        <li>
          <div class="parent {{request()->is('pks*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm "></i> PKS <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('pks*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('pks/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/create">Buat Pengajuan</a></li>
            <li class="full-click {{ request()->is('pks/pengajuan-saya') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/pengajuan-saya">Pengajuan Saya</a></li>
            <li class="full-click {{ request()->is('pks/verify') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/verify">Data PKS (Legal)</a></li>
            <li class="full-click {{ request()->is('pks/approval') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/pks/approval">Approval Direksi</a></li>
          </ul>
        </li>
        <li>
          <div class="parent {{request()->is('reports*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-folder-5-line"></i> Laporan <span class="arrow"><i class="ri ri-play-fill"></i></span>
         </div>

          <ul class="child-menu" style="{{ request()->is('reports*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('reports/ticket') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/ticket"> <i class="ri-sm ri-ticket-fill"></i> Laporan Ticketing</a></li>
            <li class="full-click {{ request()->is('reports/preventive') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/preventive"> <i class="ri-sm ri-task-fill"></i> Laporan Preventive</a></li>
            <li class="full-click {{ request()->is('reports/pks') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/pks"> <i class="ri-sm ri-shake-hands-line"></i> Laporan PKS</a></li>
            {{-- <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li> --}}
            {{-- <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li> --}}
          </ul>
        </li>
      </ul>
    </div>
