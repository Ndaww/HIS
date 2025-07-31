<div class="sidebar" id="sidebar">
      <h2>Menu</h2>
      <ul>
        <li class="menu-item"><i class="ri-sm ri-dashboard-line"></i> Dashboard</li>
        <li>
          <div class="parent {{request()->is('ticketing*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-ticket-line"></i> Ticketing <span class="arrow">▶</span>
         </div>

          <ul class="child-menu" style="{{ request()->is('ticketing*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('ticketing/create') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/create">Buat Tiket</a></li>
            <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li>
            <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li>
          </ul>
        </li>
        <li>
          <div class="parent {{request()->is('reports*') ? 'active open' : ''}}" onclick="toggleChildMenu(this)">
         <i class="ri-sm ri-folder-5-line"></i> Laporan <span class="arrow">▶</span>
         </div>

          <ul class="child-menu" style="{{ request()->is('reports*') ? 'display: block;' : '' }}">
            <li class="full-click {{ request()->is('reports/ticket') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/reports/ticket"> <i class="ri ri-ticket-line"></i> Laporan Ticketing</a></li>
            {{-- <li class="full-click {{ request()->is('ticketing') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing">Tiket Saya</a></li> --}}
            {{-- <li class="full-click {{ request()->is('ticketing/dept') ? 'active' : '' }}"> <a class="text-decoration-none text-black" href="/ticketing/dept">Semua Tiket</a></li> --}}
          </ul>
        </li>
      </ul>
    </div>