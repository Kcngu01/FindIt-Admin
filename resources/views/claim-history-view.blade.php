@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Claim History Details</h2>
                <div>
                    <a href="{{ route('claim-history.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Claim History
                    </a>
                </div>
            </div>
            <div class="alert alert-{{ $claim->status === 'approved' ? 'success' : 'danger' }} mt-2">
                This claim was <strong>{{ $claim->status === 'approved' ? 'APPROVED' : 'REJECTED' }}</strong> 
                on {{ $claim->updated_at->format('d/m/Y H:i') }} 
                by {{ $claim->admin->name ?? 'Unknown Admin' }}
            </div>
        </div>
    </div>

    <!-- Item Comparison Section -->
    <div class="row p-4">
        <div class="col-md-12 mb-4">
            <h3 class="text-center">Claim Details</h3>
        </div>

        <!-- Found Item (Left) -->
        <div class="col-md-5 mb-4">
            <div class="comparison-card p-3 rounded shadow-sm border">
                <h4 class="text-center mb-3">Found Item</h4>
                
                <!-- Found Item Image -->
                <div class="text-center mb-3">
                    @if($claim->foundItem && $claim->foundItem->image)
                        <img src="{{ asset('storage/found_items/'.$claim->foundItem->image) }}" 
                             alt="Found Item" 
                             class="item-image img-fluid mb-2" 
                             style="max-height: 200px; cursor: pointer;">
                    @else
                        <img src="{{ asset('images/placeholder.png') }}" 
                             alt="Found Item" 
                             class="item-image img-fluid mb-2" 
                             style="max-height: 200px; cursor: pointer;">
                    @endif
                </div>
                
                <!-- Found Item Details -->
                <div class="item-details p-3 border rounded mb-3">
                    <div class="text-center">
                        <div class="detail-row mb-2">
                            <strong>Name:</strong> <span>{{ $claim->foundItem->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Description:</strong> <span>{{ $claim->foundItem->description ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Category:</strong> <span>{{ $claim->foundItem->category->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Color:</strong> <span>{{ $claim->foundItem->color->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Found Location:</strong> <span>{{ $claim->foundItem->location->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Date Found:</strong> <span>{{ $claim->foundItem->created_at ? $claim->foundItem->created_at->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Middle Section -->
        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
            <div class="comparison-indicator">
                <i class="fas fa-exchange-alt fa-2x mb-3"></i>
                <div class="similarity-box p-2 text-center border rounded">
                    <div><strong>Similarity</strong></div>
                    <div class="fs-5">{{ $claim->match->similarity_score ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Lost Item (Right) -->
        <div class="col-md-5 mb-4">
            <div class="comparison-card p-3 rounded shadow-sm border">
                <h4 class="text-center mb-3">Lost Item</h4>
                
                <!-- Lost Item Image -->
                <div class="text-center mb-3">
                    @if($claim->lostItem && $claim->lostItem->image)
                        <div>
                            <img src="{{ asset('storage/lost_items/'.$claim->lostItem->image) }}" 
                                 alt="Lost Item" 
                                 class="item-image img-fluid mb-2" 
                                 style="max-height: 200px; cursor: pointer;">
                        </div>
                    @else
                        <div class="text-muted p-4 border rounded">
                            No image available
                        </div>
                    @endif
                </div>
                
                <!-- Lost Item Details -->
                @if($claim->lostItem)
                <div class="item-details p-3 border rounded mb-3">
                    <div class="text-center">
                        <div class="detail-row mb-2">
                            <strong>Name:</strong> <span>{{ $claim->lostItem->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Description:</strong> <span>{{ $claim->lostItem->description ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Category:</strong> <span>{{ $claim->lostItem->category->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Color:</strong> <span>{{ $claim->lostItem->color->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Lost Location:</strong> <span>{{ $claim->lostItem->location->name ?? '-' }}</span>
                        </div>
                        <div class="detail-row mb-2">
                            <strong>Date Lost:</strong> <span>{{ $claim->lostItem->created_at ? $claim->lostItem->created_at->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Justification Field -->
                <div class="justification-field p-3 border rounded mb-3">
                    <div class="detail-row mb-2 text-center">
                        <strong>Student Justification:</strong>
                    </div>
                    <div class="p-2 text-center">{{ $claim->student_justification ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Admin Justification Section -->
        <div class="col-md-12 mt-3">
            <div class="card border">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Admin Justification</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $claim->admin_justification ?? 'No justification provided' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Claim Metadata -->
        <div class="col-md-12 mt-4">
            <div class="card border">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Claim ID:</strong> {{ $claim->id }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $claim->status === 'approved' ? 'success' : 'danger' }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </p>
                            <p><strong>Student:</strong> {{ $claim->student->name ?? '-' }} (Matric no.: {{ $claim->student->matric_no ?? '-' }})</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Submitted On:</strong> {{ $claim->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Processed On:</strong> {{ $claim->updated_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Processed By:</strong> {{ $claim->admin->name ?? 'Unknown Admin' }} (ID: {{ $claim->admin->id ?? 'N/A' }})</p>
                        </div>
                    </div>
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
</div>
@endsection

@push('styles')
<style>
    .table-item-image {
        max-height: 50px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .table-item-image:hover {
        transform: scale(1.1);
    }
    
    .item-image {
        max-height: 200px;
        cursor: pointer;
        transition: transform 0.2s;
        border: 1px solid #dee2e6;
        padding: 3px;
        background-color: #fff;
    }
    
    .item-image:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Modal styling */
    .modal-xl {
        max-width: 90%;
    }
    
    /* Image container styles */
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
    
    /* Card styling */
    .comparison-card {
        height: 100%;
        background-color: #fff;
    }
    
    .item-details, .justification-field {
        background-color: #f8f9fa;
    }
    
    .detail-row {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .detail-row strong {
        margin-bottom: 0.25rem;
        color: #495057;
    }
    
    .similarity-box {
        background-color: #f8f9fa;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .comparison-card {
            margin-bottom: 1.5rem;
        }
        
        .similarity-box {
            margin: 1rem 0;
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
    $(document).ready(function(){
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
</script>
@endpush 