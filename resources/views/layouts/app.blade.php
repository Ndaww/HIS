<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title','RS Bunda')</title>
  <link rel="stylesheet" href="{{ asset('/assets/remixicon/remixicon.css') }}">
  <link rel="stylesheet" href="{{ asset('/assets/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset('/assets/css/style-dashboard.css') }}">
  <link href="{{ asset('/assets/datatables/datatables.min.css') }}" rel="stylesheet">
  {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css"> --}}
  <!-- DataTables CSS -->
  {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> --}}

 

</head>
<body>
@include('layouts.navbar')
  <div class="main-container">
    @include('layouts.sidebar')

    <div class="content" id="main-content">
      @yield('main-content')
    </div>


  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('active');
    }

    function toggleDropdown() {
      document.getElementById('dropdownMenu').classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('dropdownMenu');
      const profilePic = document.querySelector('.profile-pic');
      if (!dropdown.contains(event.target) && !profilePic.contains(event.target)) {
        dropdown.classList.remove('active');
      }
    });

    function loadContent(url, title) {
      fetch(url)
        .then(res => res.text())
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
          document.getElementById('page-title').innerText = title;
          document.getElementById('breadcrumb').innerHTML = title;

          // ⬇ Ubah URL di browser TANPA reload
          history.pushState({ html: html, title: title }, title, url);
        });
    }

    function toggleChildMenu(parentEl) {
        const childMenu = parentEl.nextElementSibling;
        const isVisible = childMenu.style.display === 'block';
        childMenu.style.display = isVisible ? 'none' : 'block';
        parentEl.classList.toggle('open', !isVisible);
    }

    window.addEventListener('popstate', function (event) {
      if (event.state) {
        document.getElementById('main-content').innerHTML = event.state.html;
        document.getElementById('page-title').innerText = event.state.title;
        document.getElementById('breadcrumb').innerHTML = event.state.title;
      } else {
        // Jika tidak ada state, muat default konten
        loadContent('/ajax/dashboard', 'Beranda');
      }
    });
  </script>

<script src="{{ asset('/assets/datatables/datatables.min.js') }}"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
</body>
</html>
