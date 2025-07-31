<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title','RS Bunda')</title>
  <link rel="stylesheet" href="{{ asset('/assets/remixicon/remixicon.css') }}">
  <link rel="stylesheet" href="{{ asset('/assets/bootstrap-5.0.2/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('/assets/css/style-dashboard.css') }}">
  {{-- datatable --}}
  <link href="{{ asset('/assets/datatables/datatables.min.css') }}" rel="stylesheet">
  {{-- swal --}}
  <script src="{{ asset('/assets/js/swal.js') }}"></script>
  {{-- select --}}
  <link href="{{ asset('/assets/css/select2.min.css') }}" rel="stylesheet">
</head>
<body>
@include('layouts.navbar')
  <div class="main-container">
    @include('layouts.sidebar')

    <div class="content" id="main-content">
      @yield('main-content')
      {{-- @dd(auth()->user()) --}}
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

<script src="{{ asset('/assets/bootstrap-5.0.2/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('/assets/js/jquery.js')}}"></script>
<script src="{{ asset('/assets/datatables/datatables.min.js') }}"></script>


<script src="{{ asset('/assets/js/select2.min.js') }}"></script>
{{-- <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script> --}}
{{-- <script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.2/b-3.2.4/b-html5-3.2.4/b-print-3.2.4/date-1.5.6/r-3.0.5/sc-2.4.3/sb-1.8.3/datatables.min.js" integrity="sha384-3JcIDOrqXvaMfITwX9AKEKqFhpBkUC7sB6TT1Bra08AG8DLXW4r5jTUwBp5mm5Cy" crossorigin="anonymous"></script> --}}

@yield('js')
</body>
</html>