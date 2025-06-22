<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Password Reset')</title>

    <!-- Vite Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap Icons CDN (explicit include to ensure icons work) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- common styles -->
    <style>
        /* Logo styling - centered properly */
        .logo-container {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .logo-image {
            max-width: 120px;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Main content area -->
    <main class="container mt-4">
        <!-- Centered logo -->
        <div class="logo-container">
            <img src="{{ asset('images/logo.png')}}" alt="logo" class="logo-image">
        </div>
        
        @include('partials.flash-messages')
        @yield('content')
    </main>

    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-- Script to auto-dismiss alerts -->
    <script type="module">
        $(document).ready(function(){
            setTimeout(function(){
                $('.alert').each(function(){
                    const closeButton = $(this).find('.btn-close');
                    if(closeButton.length){
                        closeButton.trigger('click');
                    }
                });
            },3000);
        });
    </script>
</body>
</html> 