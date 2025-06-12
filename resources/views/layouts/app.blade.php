<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
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
            font-size: 1rem;
        }
        
        /* Logo styling - centered properly */
        .logo-container {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 1rem 0;
        }
        
        .logo-image {
            max-width: 120px;
            height: auto;
        }
        
        /* Ensure offcanvas header layout is proper */
        .offcanvas-header {
            position: relative;
            padding: 0.5rem;
        }
        
        .offcanvas-header .btn-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        /* User info - display only style */
        .user-info-display {
            padding: 0.25rem 1.5rem;
            cursor: default;
            color: #212529;
            background-color: transparent;
            border: none;
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
        <!-- only close the dropdown when clicking outside the dropdown area     -->
        <div class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                @if(isset($admin) && $admin->profile_image)
                    <img src="{{ asset('storage/'.$admin->profile_image)}}" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                @else
                    <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif
            </div>

            <div class="dropdown-menu text-center">
            <!-- prevents the click event from bubbling up to the dropdown container, which would otherwise cause it to close -->
                <div class="user-info-display fw-bold" onclick="event.stopPropagation();">{{$userName}}</div>
                <div class="user-info-display text-secondary" onclick="event.stopPropagation();">{{$emailAddress}}</div>
                <div class="user-info-display text-secondary" onclick="event.stopPropagation();">User ID: {{Auth::id()}}</div>
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
            <!-- Centered logo -->
            <div class="logo-container">
                <img src="{{ asset('images/logo.png')}}" alt="logo" class="logo-image">
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                    <a class="nav-link" href="{{ route('claim.index') }}" data-nav-group="claim">Claim Review</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('claim-history.index') }}" data-nav-group="claim-history">Claim Approval History</a>
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
            // extracts the path portion of the current URL. The window.location.pathname property returns only the path segment of the URL (everything after the domain name and before any query parameters).
            const currentPath = window.location.pathname;

            // Check for specific page groups
            let currentNavGroup = '';
            
            // Define path patterns and their corresponding navigation groups
            const navGroupPatterns = [
                // as long as the path includes /claim-review or /claim, the "Claim Review" nav item is highlighted
                { pattern: '/claim-review/', group: 'claim' },
                { pattern: '/claim/', group: 'claim' },
                // as long as the path includes /claim-history, the "Claim Approval History" nav item is highlighted
                { pattern: '/claim-history/', group: 'claim-history' },
                // Add more patterns as needed for other sections
            ];
            
            // Determine current navigation group based on URL
            for (const pattern of navGroupPatterns) {
                // includes() which means it's checking if pattern.pattern is a substring of currentPath. So it's not requiring an exact match - it just needs currentPath to contain the string defined in pattern.pattern somewhere within it
                if (currentPath.includes(pattern.pattern)) {
                    currentNavGroup = pattern.group;
                    break;
                }
            }

            // Highlight the active nav item and expand parent if necessary
            navLinks.each(function () {
                const $link = $(this);
                // When using jQuery's .data('nav-group') on an element that doesn't have a data-nav-group attribute, it will simply return undefined rather than throwing an error. The code then safely uses this undefined value in the conditional statement that follows.
                const navGroup = $link.data('nav-group');
                
                // First check if we're in a specific nav group and this link belongs to that group
                // When you're on /claim-review/{id} (after clicking "View"), the "Claim Review" nav item is highlighted by the data-nav-group matching
                if (currentNavGroup && navGroup === currentNavGroup) {
                    $link.addClass('active');
                }
                // Otherwise use the exact URL match
                // When you're on /claim (the index page), the "Claim Review" nav item is highlighted by exact URL match
                else if (this.href === currentUrl) {
                    $link.addClass('active');
                }

                // If this link is active, expand its parent if needed
                if ($link.hasClass('active')) {
                    // Check if the active link is inside a collapsible section
                    const collapseElement = $link.closest('.collapse');
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
