@extends('layouts.app')

@section('title', 'Claim Request')

@section('content')
    <div class="container">
        <h1>Claim Review</h1>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title mb-5">Claim Request List</h5>
                @if( count($foundItems) > 0 )
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label for="facultyFilter" class="form-label">Filter by Faculty/Claim Location:</label>
                        <select class="form-select" id="facultyFilter">
                            <option value="">All Faculties</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->name }}">{{ $faculty->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto mb-2 d-flex justify-content-end align-items-end">
                        <input type="text" class="form-control" id="searchFoundItem" placeholder="ID/Name/Faculty/Claim Location">
                    </div>
                </div>
                
                <table id="foundItemTable" class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Found Item ID</th>
                            <th>Item Name</th>
                            <th>Image</th>
                            <th>Faculty/ Claim Location</th>
                            <th>Number of requests</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($foundItems as $foundItem)
                        <tr>
                            <td style="width:20%; white-space: no-wrap;">
                                <div class="d-inline-flex">
                                    <form action="{{route('claim.review', $foundItem->id)}}" method="GET" class="me-2">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success">View</button>
                                    </form>
                                </div>
                            </td>
                            <td>{{$foundItem->id}}</td>
                            <td>{{$foundItem->name}}</td>
                            <td>
                                <div style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <img src="{{ asset('storage/found_items/'.$foundItem->image) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                            </td>
                            <td>{{ $foundItem->claimLocation ? $foundItem->claimLocation->name : 'N/A' }}</td>
                            <td>{{$foundItem->claims_count}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No claim request found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
                @else
                <div class="alert alert-info mt-3">
                    <p class="mb-0">No claim request found.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        var table= $('#foundItemTable').DataTable({
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
        // adds an extra custom filtering function to DataTables' built-in search functionality,
        // extending its filtering capabilities beyond the default search behavior. (extra filter)
        // show result based on faculty filter + search keyword
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // If no faculty is selected, don't filter based on faculty
                if (!currentFacultyFilter) {
                    return true;
                }
                
                // Compare the faculty value with the selected value
                var rowFaculty = data[4]; // Faculty is in the 5th column (index 4)

                // If they match exactly (strict equality ===), the function returns true, 
                // meaning the row passes the filter and should be displayed.
                // If they don't match, it returns false, 
                // hiding the row from the filtered results.
                return rowFaculty === currentFacultyFilter;
            }
        );

        // Handle search input
        $('#searchFoundItem').on('keyup', function(){
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
    });
</script>
@endpush
