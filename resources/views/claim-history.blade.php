@extends('layouts.app')

@section('title', 'Claim Approval History')
@section('content')
    <h1 class="mb-4">Claim Approval History</h1>
<!--     
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif -->
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-5">Claim Approval List</h5>
            
            <div class="row mb-3">
                <div class="col-md-5 mb-2">
                    <label for="facultyFilter" class="form-label">Filter by Faculty:</label>
                    <select class="form-select" id="facultyFilter">
                        <option value="">All Faculties</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->name }}">{{ $faculty->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2 ms-auto d-flex justify-content-end align-items-end">
                    <input type="text" id="searchClaim" class="form-control" placeholder="ID/Matric no./Status/Faculty">
                </div>
            </div>

            <table id="claimTable" class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Claim ID</th>
                        <th>Found Item ID</th>
                        <th>Claimant Matric no.</th>
                        <th>Faculty/ Claim Location</th>
                        <!-- <th>Admin ID</th> -->
                        <th>Status</th>
                        <th>Mark as Claimed</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($claims as $claim)
                    <tr>
                        <td>
                            <form method="GET" action="{{ route('claim-history.view', $claim->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary" data-id="{{$claim->id}}">View</button>
                            </form>
                        </td>
                        <td>{{$claim->id}}</td>
                        <td>{{$claim->found_item_id}}</td>
                        <td>{{$claim->student->matric_no}}</td>
                        <td>{{ $claim->foundItem && $claim->foundItem->claimLocation ? $claim->foundItem->claimLocation->name : 'N/A' }}</td>
                        <!-- <td>{{$claim->admin_id}}</td> -->
                        <td>
                            <span class="badge bg-{{ 
                                $claim->status === 'approved' ? 'success' : 
                                ($claim->status === 'claimed' ? 'primary' : 'danger') 
                            }}">
                                {{ ucfirst($claim->status) }}
                            </span>
                        </td>
                        <td>
                            @if($claim->status === 'approved')
                                <button type="button" class="btn btn-sm btn-primary mark-as-claimed-btn" 
                                    data-claim-id="{{ $claim->id }}" 
                                    data-claim-matric="{{ $claim->student->matric_no }}"
                                    data-claim-item="{{ $claim->foundItem->name ?? 'Unknown item' }}">
                                    Mark as Claimed
                                </button>
                            @elseif($claim->status === 'claimed')
                                <span class="text-success"><i class="fas fa-check"></i> Collected</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Mark as Claimed Confirmation Modal -->
    <div class="modal fade" id="markAsClaimedModal" tabindex="-1" aria-labelledby="markAsClaimedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="markAsClaimedModalLabel">Confirm Item Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone.
                    </div>
                    <p>Are you sure the student has collected this item?</p>
                    <div class="mb-3">
                        <strong>Item:</strong> <span id="modalItemName"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Student Matric No:</strong> <span id="modalMatricNo"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="markAsClaimedForm" method="POST" action="">
                        @csrf
                        <button type="submit" class="btn btn-primary">Confirm Collection</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        
        var table = $('#claimTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5,10,20,50],
            "dom": 'lrtip',
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' } // Apply to all columns
            ]
        });

        // Store the current faculty filter value
        var currentFacultyFilter = '';
        
        // Create a custom filtering function for the faculty filter
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // If no faculty is selected, don't filter based on faculty
                if (!currentFacultyFilter) {
                    return true;
                }
                
                // Compare the faculty value with the selected value
                var rowFaculty = data[4]; // Faculty is in the 5th column (index 4)
                return rowFaculty === currentFacultyFilter;
            }
        );

        // Handle search input
        $('#searchClaim').on('keyup', function(){
            // Apply the search filter
            table.search($(this).val()).draw();
            // The faculty filter will still be applied because it's in the ext.search array
        });

        // Handle faculty filter changes
        $('#facultyFilter').on('change', function() {
            // Update the current faculty filter value
            currentFacultyFilter = $(this).val();
            
            // Redraw the table to apply both filters
            // (search filter is maintained by DataTables, faculty filter is in ext.search)
            table.draw();
        });
        
        // Initialize the modal
        var markAsClaimedModal = new bootstrap.Modal(document.getElementById('markAsClaimedModal'));
        
        // Handle Mark as Claimed button click
        $('.mark-as-claimed-btn').on('click', function() {
            var claimId = $(this).data('claim-id');
            var matricNo = $(this).data('claim-matric');
            var itemName = $(this).data('claim-item');
            
            // Update modal content
            $('#modalMatricNo').text(matricNo);
            $('#modalItemName').text(itemName);
            
            // Update form action
            $('#markAsClaimedForm').attr('action', '{{ route("claim-history.mark-as-claimed", "") }}/' + claimId);
            
            // Show the modal
            markAsClaimedModal.show();
        });
    });
</script>
@endpush