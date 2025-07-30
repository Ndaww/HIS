<div class="navbar">
    <div class="brand"><i class="ri-hospital-line"></i> RS Dashboard</div>
    <div class="right-section">
      <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
      <img src="https://i.pravatar.cc/100?img=68" class="profile-pic" onclick="toggleDropdown()" />
      <div class="dropdown" id="dropdownMenu">
        <a href="#"><i class="ri ri-profile-line"></i> My Profile</a>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn"><i class="ri ri-logout-box-line"></i> Logout</button>
      </form>

      </div>
    </div>
  </div>