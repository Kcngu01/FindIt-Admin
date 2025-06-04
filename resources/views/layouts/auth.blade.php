<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Password Reset')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    <!-- datatables -->
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

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