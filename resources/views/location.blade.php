@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Location</h1>
        <div class="d-flex justify-content-end">
            <button type="button" data-bs-toggle="modal" data-bs-target="#addLocationModal" class="btn btn-secondary mt-3 float-end">
                <i class="bi bi-plus"></i>Add Location
            </button> 
        </div>

        <!-- Add Location Modal -->
        <div class="modal fade" id="addLocationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLocationModalLabel">Add New Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" arial-label="Close"></button>
                    </div>
                    <form action="{{route('location.store')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="locationName" class="form-label">Location Name</label>
                                <input type="text" class="form-control" id="locationName" name="name" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>            
        </div>

        <!-- Edit Location modal -->
        <div class="modal fade" id="editLocationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editLocationModalLabel">Edit Location</h5>
                    </div>
                    <form id="editLocationForm" action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="locationName" class="form-label">Location Name</label>
                                <input type="text" class="form-control" id="editLocationName" name="name" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Location List</h5>
                @if( count($locations) > 0 )
                <div class="mb-3 search-bar d-inline-block float-end">
                    <!-- <div class="input-group"> -->
                        <input type="text" class="form-control" id="searchLocation" placeholder="ID/Name">
                    <!-- </div> -->
                </div>
                <table id="locationTable" class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Location ID</th>
                            <th>Location Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($locations as $location)
                        <tr>
                            <td style="width:20%; white-space: no-wrap;">
                                <div class="d-inline-flex">
                                    <form action="{{route('location.destroy', $location->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this location?');" class="me-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                                    </form>
                                    <button type="button" class="btn btn-outline-secondary edit-location-btn" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editLocationModal"
                                            data-id="{{$location->id}}"
                                            data-name="{{$location->name}}">Edit
                                    </button>
                                </div>
                            </td>
                            <td>{{$location->id}}</td>
                            <td>{{$location->name}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No locations found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
                @else
                <div class="alert alert-info mt-3">
                    <p class="mb-0">No locations found. Please add a new location using the "Add Location" button above.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        var table= $('#locationTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5,10,20,50],
            "dom": 'lrtip',
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' } // Apply to all columns
            ]
        });

        $('#searchLocation').on('keyup', function(){
            table.search($(this).val()).draw();
        });

        $('.edit-location-btn').on('click',function(){
            const id= $(this).data('id');
            const name= $(this).data('name');
            $('#editLocationName').val(name);
            $('#editLocationForm').attr('action', "{{ route('location.update', '') }}/" + id);
        });
    });
</script>
@endpush
