<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- common styles -->
    <style>
        /* Highlight active nav item */
        .nav-link.active {
            background-color: #007bff;
            color: white;
        }

        /* Arrow styling */
        .arrow {
            transition: transform 0.3s ease;
        }

        .arrow.collapsed {
            transform: rotate(0deg);
        }

        .arrow:not(.collapsed) {
            transform: rotate(180deg);
        }

        /* Submenu styling */
        .collapse .nav-link {
            font-size: 0.9rem;
        }
    </style>

    <!-- custom styles -->
    @stack('styles')

    <!-- datatables -->
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

</head>
<body>
    <!-- Toggle button for hamburger icon -->
    <nav class="d-flex justify-content-between border-bottom position-sticky top-0 bg-white shadow-sm" style="z-index: 1030;">
        <button class="btn btn-primary " type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample" >
        <i class="bi bi-list"></i>
        </button>
        <!-- User profile picture placeholder using only Bootstrap classes -->
        <div class="dropdown me-2">
            <div class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                @if(isset($admin) && $admin->profile_image)
                    <img src="{{ asset('storage/'.$admin->profile_image)}}" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                @else
                    <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif
            </div>

            <div class="dropdown-menu text-center ">
                <h5 class="dropdown-item">{{$userName}}</h6>
                <h6 class="dropdown-item text-secondary">{{$emailAddress}}</h6>
                <h6 class="dropdown-item text-secondary">User ID:{{Auth::id()}}</h6>
                <hr class="dropdown-divider">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="bi bi-box-arrow-right">Logout</i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Offcanvas component for navigation -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <!-- <h5 class="offcanvas-title" id="offcanvasExampleLabel">Navigation</h5> -->
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="logo">
                <img src="{{ asset('images/logo.png')}}" alt="logo" class="logo-image">
            </div>
            <button type="button" class="btn-close align-self-start" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" aria-current="page" href="{{route('dashboard')}}">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('students.index')}}">Student</a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#itemCharacteristics" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="itemCharacteristics">
                Item Characteristics
                <i class="bi bi-chevron-down arrow"></i>
            </a>
            <div class="collapse" id="itemCharacteristics">
                <ul class="nav flex-column ps-3">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('category.index')}}">Category</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('colour.index')}}">Colour</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('location.index')}}">Location</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('claim.index') }}">Claim Review</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('claim-history.index') }}">Claim Approval History</a>
        </li>   
        </ul>
    </div>
    </div>

    <!-- Main content area to be pushed -->
    <main class="container mt-4">
        @include('partials.flash-messages')
        @yield('content')
    </main>

    <!-- common scripts -->
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

            // Get all nav links
            const navLinks = $('.nav-link');
            const currentUrl = window.location.href;

            // Highlight the active nav item and expand parent if necessary
            navLinks.each(function () {
                if (this.href === currentUrl) {
                    $(this).addClass('active');

                    // Check if the active link is inside a collapsible section
                    const collapseElement = $(this).closest('.collapse');
                    if (collapseElement.length) {
                        const parentLink = collapseElement.prev('.nav-link'); // The parent nav-link
                        const arrow = parentLink.find('.arrow');

                        // Expand the collapsible section
                        collapseElement.addClass('show');
                        parentLink.attr('aria-expanded', 'true');
                        arrow.removeClass('bi-chevron-down collapsed').addClass('bi-chevron-up');
                    }
                }
            });

            // Handle arrow toggle for all collapsible sections
            const collapsibleLinks = $('.nav-link[data-bs-toggle="collapse"]');
            collapsibleLinks.each(function () {
                const collapseId = $(this).attr('href'); // e.g., #itemCharacteristics
                const collapseElement = $(collapseId);
                const arrow = $(this).find('.arrow');

                // Listen for Bootstrap collapse events
                collapseElement.on('show.bs.collapse', function () {
                    arrow.removeClass('bi-chevron-down collapsed').addClass('bi-chevron-up');
                });

                collapseElement.on('hide.bs.collapse', function () {
                    arrow.removeClass('bi-chevron-up').addClass('bi-chevron-down collapsed');
                });
            });
        });
     </script>

     <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap JS - Ensure it's loaded after jQuery but before custom scripts -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->

    <!-- Ensure Bootstrap Modal functionality is available -->
    <script>
        // Debug script to check if Bootstrap is properly loaded via Vite
        document.addEventListener('DOMContentLoaded', function() {
            console.log('==== BOOTSTRAP DEBUGGING ====');
            console.log('Bootstrap available globally:', typeof bootstrap !== 'undefined');
            
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap version:', bootstrap.Tooltip?.VERSION || 'Unknown');
                console.log('Modal constructor available:', typeof bootstrap.Modal === 'function');
                console.log('Tooltip constructor available:', typeof bootstrap.Tooltip === 'function');
                console.log('Popover constructor available:', typeof bootstrap.Popover === 'function');
                console.log('jQuery available:', typeof jQuery !== 'undefined');
                console.log('==== BOOTSTRAP LOADED SUCCESSFULLY ====');
                
                // Initialize all modals
                var modals = document.querySelectorAll('.modal');
                modals.forEach(function(modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    // Store the modal instance on the element for future use
                    modalEl._bootstrapModal = modal;
                });
            } else {
                console.error('Bootstrap is NOT defined! Check the console for more details.');
                console.error('==== BOOTSTRAP LOADING FAILED ====');
            }
        });
    </script>
     
    <!-- custom scripts -->
    @stack('scripts')
</body>
</html>
