@extends('layouts.app')

@section('title', 'Claim Review')
@section('content')
<div class="container">
    <h2 class="mb-4">Claim Review</h2>
    
    <!-- Hidden CSRF token for AJAX requests -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <!-- Item Comparison Section -->
    <div class="card mb-4" id="comparisonSection">
        <div class="card-body">
            <h5 class="card-title text-center mb-4">Item Comparison</h5>
            
            <div class="row">
                <!-- Found Item Column -->
                <div class="col-md-6 border-end">
                    <h6 class="text-center mb-3">Found Item</h6>
                    <div class="text-center mb-3">
                        @if($foundItem->image)
                            <img id="foundItemImage" src="{{ asset('storage/found_items/'.$foundItem->image) }}" alt="Found Item Image" class="item-image" style="width: 200px; height: 200px; object-fit: cover; cursor: pointer;">
                        @else
                            <img id="foundItemImage" src="{{ asset('images/placeholder.png') }}" alt="Found Item Image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                    </div>
                    
                    <div class="item-details-container">
                        <div class="item-details">  
                            <p class="mb-2"><span class="fw-bold">Name: </span><span id="foundItemName">{{$foundItem->name}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Description: </span><span id="foundItemDescription">{{$foundItem->description??'-'}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Category: </span><span id="foundItemCategory">{{$foundItem->category->name}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Colour: </span><span id="foundItemColor">{{$foundItem->color->name}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Location: </span><span id="foundItemLocation">{{$foundItem->location->name}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Faculty (Claim Location): </span><span id="foundItemClaimLocation">{{$foundItem->claimLocation->name ?? '-'}}</span></p>
                            <p class="mb-2"><span class="fw-bold">Date Found: </span><span id="foundItemDate">{{$foundItem->created_at->format('d/m/Y')}}</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Lost Item Column -->
                <div class="col-md-6">
                    <h6 class="text-center mb-3">Lost Item</h6>
                    
                    <!-- Image section - will be replaced with text when needed -->
                    <div class="text-center mb-3" id="lostItemImageContainer">
                        <img id="lostItemImage" src="{{ asset('images/placeholder.png') }}" alt="Lost Item Image" class="item-image" style="width: 200px; height: 200px; object-fit: cover; cursor: pointer;">
                    </div>
                    
                    <!-- No-image text display area -->
                    <div class="text-center mb-3" id="noImageText" style="display: none;">
                        <div class="alert alert-secondary" style="width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                            <span>No image available</span>
                        </div>
                    </div>
                    
                    <!-- Lost item details - will be hidden when no lost item -->
                    <div class="item-details-container" id="lostItemDetails">
                        <div class="item-details">
                            <p class="mb-2"><span class="fw-bold">Name: </span><span id="lostItemName">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Description: </span><span id="lostItemDescription">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Category: </span><span id="lostItemCategory">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Colour: </span><span id="lostItemColor">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Similarity Score: </span><span id="similarityScore">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Location: </span><span id="lostItemLocation">-</span></p>
                            <p class="mb-2"><span class="fw-bold">Date Lost: </span><span id="lostItemDate">-</span></p>
                        </div>
                    </div>
                    
                    <!-- Justification field - always present, just shown conditionally -->
                    <div class="item-details-container justification-field" style="display: none;">
                        <div class="item-details">
                            <p class="mb-1"><span class="fw-bold">Justification: </span><span id="justification">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mt-3">
                <div class="col-12 text-end">
                    <button class="btn btn-secondary me-2" id="rejectButton" disabled>Reject</button>
                    <button class="btn btn-success" id="approveButton" disabled>Approve</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Item Image</h5>
                    <div class="zoom-controls ms-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="zoomOut">
                            <i class="bi bi-zoom-out"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomIn">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="resetZoom">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div class="image-container" style="overflow: auto; height: 80vh; position: relative;">
                        <img id="modalImage" src="" alt="Item Image" class="img-fluid" style="transform-origin: center; transition: transform 0.2s ease-in-out;">
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Use the mouse wheel or pinch gestures to zoom. Drag to pan when zoomed in.</small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmActionTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmActionMessage" class="mb-3">Are you sure you want to proceed with this action?</p>
                    
                    <div class="form-group">
                        <label for="adminJustification" class="form-label">Admin Justification (Optional):</label>
                        <textarea id="adminJustification" class="form-control" rows="3" placeholder="Enter your justification here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmActionButton">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Claims Request List Section -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Claims Request List</h5>
                <div>
                    <input type="text" class="form-control" id="searchLostItem" placeholder="ID/Name/Matric no./Faculty">
                </div>
            </div>

            <div class="table-responsive">
                <table id="lostItemTable" class="table">
                    <thead class="bg-light">
                        <tr>
                            <th>Action</th>
                            <th>Claim ID</th>
                            <th>Lost Item ID</th>
                            <th>Match ID</th>
                            <th>Item Name</th>
                            <th>Image</th>
                            <th>Claimant Matric no.</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($claims as $claim)
                        <tr data-claim-id="{{ $claim->id }}">
                            <td>
                                <button class="btn btn-secondary btn-sm compare-btn" data-claim-id="{{ $claim->id }}">Compare</button>
                            </td>
                            <td>{{$claim->id}}</td>
                            <td>{{$claim->lostItem->id ?? '-'}}</td>
                            <td>{{$claim->match->id ?? '-'}}</td>
                            <td>{{$claim->lostItem->name ?? '-'}}</td>
                            <td>
                                @if($claim->lostItem && $claim->lostItem->image)
                                    <div style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden">
                                        <img src="{{ asset('storage/lost_items/'.$claim->lostItem->image) }}" alt="Item Image" class="table-item-image" style="max-width: 100%; max-height: 100%; object-fit: contain; cursor: pointer;" data-src="{{ asset('storage/lost_items/'.$claim->lostItem->image) }}">
                                    </div>
                                @else
                                    <div style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden">
                                        <p>-</p>
                                    </div>
                                @endif
                            </td>
                            <td>{{$claim->student->matric_no}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No claim request found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    /* Custom vertical divider */
    .border-end {
        border-right: 1px solid #dee2e6;
    }
    
    /* Item details styling */
    .item-details-container {
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .item-details {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px 15px;
        width: 100%;
        max-width: 350px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        background-color: #f8f9fa;
    }
    
    /* Image styling */
    .item-image, .table-item-image {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .item-image:hover, .table-item-image:hover {
        transform: scale(1.05);
    }
    
    /* Modal image container styles */
    .image-container {
        cursor: zoom-in;
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .image-container.zoomed {
        cursor: grab;
    }
    
    .image-container.grabbing {
        cursor: grabbing;
    }
    
    /* Zoom controls styling */
    .zoom-controls {
        display: flex;
        align-items: center;
    }
    
    .zoom-controls .btn {
        padding: 0.25rem 0.5rem;
    }
    
    /* Modal styling */
    .modal-xl {
        max-width: 90%;
    }
    
    /* Confirmation modal styling */
    #confirmActionModal .modal-body {
        padding: 1.5rem;
        text-align: center;
    }
    
    #confirmActionMessage {
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    
    #confirmActionModal .form-group {
        margin-top: 1.5rem;
        text-align: left;
    }
    
    #adminJustification {
        resize: vertical;
        min-height: 80px;
        border-color: #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    #adminJustification:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #confirmActionModal .modal-footer {
        justify-content: center;
        padding: 1rem 1.5rem 1.5rem;
        border-top: none;
    }
    
    #confirmActionModal .btn {
        min-width: 100px;
        font-weight: 500;
    }
    
    /* For responsive design on smaller screens */
    @media (max-width: 767.98px) {
        .border-end {
            border-right: none;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        
        .item-details {
            max-width: 100%;
        }
        
        .modal-xl {
            max-width: 100%;
            margin: 0.5rem;
        }
        
        .zoom-controls {
            margin-right: auto;
            margin-left: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Helper function to safely get the CSRF token
    function getCsrfToken() {
        // Try to get from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // If meta tag isn't available, try to get from a hidden input field (Laravel forms have this)
        const csrfInput = document.querySelector('input[name="_token"]');
        if (csrfInput) {
            return csrfInput.value;
        }
        
        // For Laravel, check if there's a Laravel global object with token
        if (window.Laravel && window.Laravel.csrfToken) {
            return window.Laravel.csrfToken;
        }
        
        // If we still can't find it, you might need to handle this case
        console.warn('CSRF token not found. Form submission may fail.');
        return ''; // Return empty string as fallback
    }

    $(document).ready(function(){
        // Initialize DataTable
        var table = $('#lostItemTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 20, 50],
            "dom": 'lrtip', // This removes the default search box
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' }
            ]
        });

        // Ensure action buttons are disabled on page load
        $('#approveButton, #rejectButton').prop('disabled', true);
        
        // Add tooltip to explain why buttons are disabled
        $('#approveButton, #rejectButton').attr('title', 'Select an item to compare first');
        
        // Add visual indicator for disabled buttons
        updateButtonState();

        // Custom search box
        $('#searchLostItem').on('keyup', function(){
            table.search($(this).val()).draw();
        });

        // Handle compare button clicks
        $('.compare-btn').on('click', function() {
            const claimId = $(this).data('claim-id');
            fetchComparisonData(claimId);
            
            // Scroll to comparison section
            $('html, body').animate({
                scrollTop: $('#comparisonSection').offset().top - 20
            }, 500);
            
            // Update button states with loading indicator
            $('#approveButton, #rejectButton').prop('disabled', true)
                .attr('title', 'Loading comparison data...');
            updateButtonState();
        });
        
        // Handle approve/reject buttons
        $('#approveButton').on('click', function() {
            const claimId = $(this).data('claim-id');
            
            // Show confirmation dialog using Bootstrap modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
            $('#confirmActionTitle').text('Confirm Approval');
            $('#confirmActionMessage').text('Are you sure you want to approve this claim?');
            $('#confirmActionButton').text('Approve').removeClass('btn-danger').addClass('btn-success');
            
            // Set up the action for when the confirm button is clicked
            $('#confirmActionButton').off('click').on('click', function() {
                // Close the confirmation modal
                confirmModal.hide();
                
                // Here you would implement the approve logic with the claim ID
                console.log('Approving claim ID:', claimId);
                alert('Approving claim ID: ' + claimId);
                
                // Make an AJAX call to the backend to accept the claim
                fetch('{{ route("claim.accept") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        claimId: claimId,
                        adminJustification: $('#adminJustification').val()
                    })
                })
                .then(response => {
                    // Log the status for debugging
                    console.log('Response status:', response.status);
                    
                    // Even if status is not 200, try to parse the response as JSON
                    return response.json().then(data => {
                        // Attach the status to the parsed data
                        return { ...data, status: response.status };
                    }).catch(e => {
                        // If JSON parsing fails, create a generic error object
                        return { 
                            success: false, 
                            message: 'Invalid response format', 
                            status: response.status 
                        };
                    });
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    // Check if the response indicates success
                    if (data.success) {
                        // Show success message and reload the page
                        alert(data.message || 'Claim approved successfully!');
                        window.location.reload();
                    } else {
                        // If we got a response but success is false
                        throw new Error(data.message || 'Server returned an error');
                    }
                })
                .catch(error => {
                    console.error('Error approving claim:', error);
                    alert('Failed to approve claim. Please try again. ' + (error.message || ''));
                });
            });
            
            // Show the confirmation modal
            confirmModal.show();
        });
        
        $('#rejectButton').on('click', function() {
            const claimId = $(this).data('claim-id');
            
            // Show confirmation dialog using Bootstrap modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
            $('#confirmActionTitle').text('Confirm Rejection');
            $('#confirmActionMessage').text('Are you sure you want to reject this claim?');
            $('#confirmActionButton').text('Reject').removeClass('btn-success').addClass('btn-danger');
            
            // Set up the action for when the confirm button is clicked
            $('#confirmActionButton').off('click').on('click', function() {
                // Close the confirmation modal
                confirmModal.hide();
                
                // Here you would implement the reject logic with the claim ID
                // console.log('Rejecting claim ID:', claimId);
                // alert('Rejecting claim ID: ' + claimId);
                
                // Make an AJAX call to the backend to reject the claim
                fetch('{{ route("claim.reject") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        claimId: claimId,
                        adminJustification: $('#adminJustification').val()
                    })
                })
                .then(response => {
                    // Log the status for debugging
                    console.log('Response status:', response.status);
                    
                    // Even if status is not 200, try to parse the response as JSON
                    return response.json().then(data => {
                        // Attach the status to the parsed data
                        return { ...data, status: response.status };
                    }).catch(e => {
                        // If JSON parsing fails, create a generic error object
                        return { 
                            success: false, 
                            message: 'Invalid response format', 
                            status: response.status 
                        };
                    });
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    // Check if the response indicates success
                    if (data.success) {
                        // Show success message and reload the page
                        alert(data.message || 'Claim rejected successfully!');
                        window.location.reload();
                    } else {
                        // If we got a response but success is false
                        throw new Error(data.message || 'Server returned an error');
                    }
                })
                .catch(error => {
                    console.error('Error rejecting claim:', error);
                    alert('Failed to reject claim. Please try again. ' + (error.message || ''));
                });
            });
            
            // Show the confirmation modal
            confirmModal.show();
        });
        
        // Get reference to the modal
        const imageModal = document.getElementById('imageModal');
        let bsImageModal;
        
        // Initialize the modal if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            bsImageModal = new bootstrap.Modal(imageModal);
        } else {
            console.warn('Bootstrap not found. Will try to use jQuery modal method.');
        }
        
        // Image zoom and pan functionality
        let scale = 1;
        const MAX_SCALE = 4;
        const MIN_SCALE = 0.5;
        const ZOOM_STEP = 0.25;
        let isDragging = false;
        let startX, startY, startTranslateX = 0, startTranslateY = 0;
        let currentTranslateX = 0, currentTranslateY = 0;
        
        // Reset zoom and position when modal is opened
        $(imageModal).on('show.bs.modal', function() {
            resetImageZoom();
        });
        
        // Zoom in button
        $('#zoomIn').on('click', function() {
            if (scale < MAX_SCALE) {
                scale += ZOOM_STEP;
                updateImageTransform();
            }
        });
        
        // Zoom out button
        $('#zoomOut').on('click', function() {
            if (scale > MIN_SCALE) {
                scale -= ZOOM_STEP;
                updateImageTransform();
                
                // If zoomed out completely, reset position
                if (scale <= 1) {
                    currentTranslateX = 0;
                    currentTranslateY = 0;
                    updateImageTransform();
                }
            }
        });
        
        // Reset zoom button
        $('#resetZoom').on('click', function() {
            resetImageZoom();
        });
        
        function resetImageZoom() {
            scale = 1;
            currentTranslateX = 0;
            currentTranslateY = 0;
            updateImageTransform();
        }
        
        function updateImageTransform() {
            const modalImage = document.getElementById('modalImage');
            modalImage.style.transform = `scale(${scale}) translate(${currentTranslateX}px, ${currentTranslateY}px)`;
        }
        
        // Mouse wheel zoom
        $('.image-container').on('wheel', function(e) {
            e.preventDefault();
            
            // Get mouse position relative to image
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Determine zoom direction
            if (e.originalEvent.deltaY < 0) {
                // Zoom in
                if (scale < MAX_SCALE) {
                    scale += ZOOM_STEP;
                }
            } else {
                // Zoom out
                if (scale > MIN_SCALE) {
                    scale -= ZOOM_STEP;
                    
                    // If zoomed out completely, reset position
                    if (scale <= 1) {
                        currentTranslateX = 0;
                        currentTranslateY = 0;
                    }
                }
            }
            
            updateImageTransform();
        });
        
        // Drag to pan when zoomed in
        $('.image-container').on('mousedown touchstart', function(e) {
            if (scale > 1) {
                isDragging = true;
                
                if (e.type === 'mousedown') {
                    startX = e.clientX;
                    startY = e.clientY;
                } else {
                    startX = e.originalEvent.touches[0].clientX;
                    startY = e.originalEvent.touches[0].clientY;
                }
                
                startTranslateX = currentTranslateX;
                startTranslateY = currentTranslateY;
                
                $(this).css('cursor', 'grabbing');
                e.preventDefault();
            }
        });
        
        $(document).on('mousemove touchmove', function(e) {
            if (isDragging && scale > 1) {
                let clientX, clientY;
                
                if (e.type === 'mousemove') {
                    clientX = e.clientX;
                    clientY = e.clientY;
                } else {
                    clientX = e.originalEvent.touches[0].clientX;
                    clientY = e.originalEvent.touches[0].clientY;
                }
                
                // Calculate the distance moved
                const deltaX = (clientX - startX) / scale;
                const deltaY = (clientY - startY) / scale;
                
                // Update the translation
                currentTranslateX = startTranslateX + deltaX;
                currentTranslateY = startTranslateY + deltaY;
                
                updateImageTransform();
                e.preventDefault();
            }
        });
        
        $(document).on('mouseup touchend', function() {
            if (isDragging) {
                isDragging = false;
                $('.image-container').css('cursor', 'auto');
            }
        });
        
        // Handle pinch gestures for touch devices
        let initialDistance = 0;
        let initialScale = 1;
        
        $('.image-container').on('touchstart', function(e) {
            if (e.originalEvent.touches.length === 2) {
                e.preventDefault();
                
                const touch1 = e.originalEvent.touches[0];
                const touch2 = e.originalEvent.touches[1];
                
                initialDistance = Math.hypot(
                    touch2.clientX - touch1.clientX,
                    touch2.clientY - touch1.clientY
                );
                
                initialScale = scale;
            }
        });
        
        $('.image-container').on('touchmove', function(e) {
            if (e.originalEvent.touches.length === 2) {
                e.preventDefault();
                
                const touch1 = e.originalEvent.touches[0];
                const touch2 = e.originalEvent.touches[1];
                
                const currentDistance = Math.hypot(
                    touch2.clientX - touch1.clientX,
                    touch2.clientY - touch1.clientY
                );
                
                // Calculate new scale
                const ratio = currentDistance / initialDistance;
                scale = Math.min(Math.max(initialScale * ratio, MIN_SCALE), MAX_SCALE);
                
                updateImageTransform();
            }
        });
        
        // Handle image clicks to show modal with larger image
        $(document).on('click', '.item-image', function() {
            console.log('Item image clicked');
            const imgSrc = $(this).attr('src');
            $('#modalImage').attr('src', imgSrc);
            
            // Show the modal
            showImageModal();
        });
        
        // Handle table image clicks
        $(document).on('click', '.table-item-image', function() {
            console.log('Table image clicked');
            const imgSrc = $(this).data('src');
            $('#modalImage').attr('src', imgSrc);
            
            // Show the modal
            showImageModal();
        });
        
        // Function to show the modal using various methods (for compatibility)
        function showImageModal() {
            // Try multiple methods to ensure the modal opens
            if (bsImageModal) {
                // Use Bootstrap 5 method if available
                bsImageModal.show();
            } else if (typeof bootstrap !== 'undefined') {
                // Alternative Bootstrap 5 approach
                new bootstrap.Modal(imageModal).show();
            } else if ($.fn.modal) {
                // Fallback to jQuery method
                $('#imageModal').modal('show');
            } else {
                console.error('No method available to show modal!');
                alert('Image preview is not available. Please check console for details.');
            }
        }
    });
    
    // Function to fetch and display comparison data
    function fetchComparisonData(claimId) {
        // Show loading indicator
        $('#comparisonSection').addClass('position-relative').append(
            '<div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75">' +
            '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' +
            '</div>'
        );
        
        // Disable action buttons while loading
        $('#approveButton, #rejectButton').prop('disabled', true);
        
        // Fetch the comparison data from the server
        fetch(`{{ route('claim.comparison', '') }}/${claimId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Remove loading indicator
                $('#comparisonSection .position-absolute').remove();
                $('#comparisonSection').removeClass('position-relative');
                
                // Handle the justification text (always needed)
                $('#justification').text(data.lost.justification);
                
                // Handle image display for lost item
                if (data.lost.image === 'no_image') {
                    // Show "No image available" text instead of image
                    $('#lostItemImageContainer').hide();
                    $('#noImageText').show();
                } else {
                    // Show the actual image
                    $('#lostItemImageContainer').show();
                    $('#noImageText').hide();
                    $('#lostItemImage').attr('src', data.lost.image);
                }
                
                // Handle display based on whether there's a lost item
                if (!data.has_lost_item) {
                    // For claims without lost items, hide details and show only justification
                    $('#lostItemDetails').hide();
                    $('.justification-field').show();
                    
                    // Enable buttons only if there's a valid claim to act on
                    const hasJustification = data.lost.justification && data.lost.justification.trim() !== '-' && data.lost.justification.trim() !== '';
                    $('#approveButton, #rejectButton').prop('disabled', !hasJustification);
                    updateButtonState();
                } else {
                    // For claims with lost items, show all details and hide justification
                    $('#lostItemDetails').show();
                    $('.justification-field').show();
                    
                    // Update lost item details
                    $('#lostItemName').text(data.lost.name);
                    $('#lostItemDescription').text(data.lost.description);
                    $('#lostItemCategory').text(data.lost.category);
                    $('#lostItemColor').text(data.lost.color);
                    $('#lostItemLocation').text(data.lost.location);
                    $('#lostItemDate').text(data.lost.date);
                    $('#similarityScore').text(data.lost.similarity_score);
                    
                    // Enable buttons since we have valid data
                    $('#approveButton, #rejectButton').prop('disabled', false);
                    updateButtonState();
                }
                
                // Store the current claim ID for approve/reject actions
                $('#approveButton, #rejectButton').data('claim-id', data.claim_id);
            })
            .catch(error => {
                // Remove loading indicator and show error
                $('#comparisonSection .position-absolute').remove();
                $('#comparisonSection').removeClass('position-relative');
                console.error('Error fetching comparison data:', error);
                alert('Failed to load comparison data. Please try again.');
                
                // Keep buttons disabled on error
                $('#approveButton, #rejectButton').prop('disabled', true);
                updateButtonState();
            });
    }

    // Function to update the visual state of approve/reject buttons
    function updateButtonState() {
        // For approve button
        if ($('#approveButton').prop('disabled')) {
            $('#approveButton').addClass('opacity-50')
                .css('cursor', 'not-allowed')
                .attr('title','Select an item to compare first');
        } else {
            $('#approveButton').removeClass('opacity-50')
                .css('cursor', 'pointer')
                .attr('title', 'Approve this claim');
        }
        
        // For reject button
        if ($('#rejectButton').prop('disabled')) {
            $('#rejectButton').addClass('opacity-50')
                .css('cursor', 'not-allowed')
                .attr('title','Select an item to compare first');
        } else {
            $('#rejectButton').removeClass('opacity-50')
                .css('cursor', 'pointer')
                .attr('title', 'Reject this claim');
        }
    }
</script>
@endpush