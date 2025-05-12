@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Colour</h1>
        <div class="d-flex justify-content-end">
            <button type="button" data-bs-toggle="modal" data-bs-target="#addColourModal" class="btn btn-secondary mt-3 float-end">
                <i class="bi bi-plus"></i>Add Colour
            </button> 
        </div>

        <!-- Add Colour Modal -->
        <div class="modal fade" id="addColourModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addColourModalLabel">Add New Colour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" arial-label="Close"></button>
                    </div>
                    <form action="{{route('colour.store')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="colourName" class="form-label">Colour Name</label>
                                <input type="text" class="form-control" id="colourName" name="name" required>
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

        <!-- Edit Colour modal -->
        <div class="modal fade" id="editColourModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editColourModalLabel">Edit Colour</h5>
                    </div>
                    <form id="editColourForm" action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="colourName" class="form-label">Colour Name</label>
                                <input type="text" class="form-control" id="editColourName" name="name" required>
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
                <h5 class="card-title">Colour List</h5>
                @if(count($colours) > 0)
                <div class="mb-3 search-bar d-inline-block float-end">
                    <!-- <div class="input-group"> -->
                        <input type="text" class="form-control" id="searchColour" placeholder="ID/Name">
                    <!-- </div> -->
                </div>
                <table id="colourTable" class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Colour ID</th>
                            <th>Colour Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($colours as $colour)
                        <tr>
                            <td style="width:20%; white-space: no-wrap;">
                                <div class="d-inline-flex">
                                    <form action="{{route('colour.destroy', $colour->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this colour?');" class="me-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                                    </form>
                                    <button type="button" class="btn btn-outline-secondary edit-colour-btn" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editColourModal"
                                            data-id="{{$colour->id}}"
                                            data-name="{{$colour->name}}">Edit
                                    </button>
                                </div>
                            </td>
                            <td>{{$colour->id}}</td>
                            <td>{{$colour->name}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No colours found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
                @else
                <div class="alert alert-info mt-3">
                    <p class="mb-0">No colours found. Please add a new colour using the "Add Colour" button above.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        var table= $('#colourTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5,10,20,50],
            "dom": 'lrtip',
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' } // Apply to all columns
            ]
        });

        $('#searchColour').on('keyup', function(){
            table.search($(this).val()).draw();
        });

        $('.edit-colour-btn').on('click',function(){
            var id= $(this).data('id');
            var name= $(this).data('name');
            $('#editColourName').val(name);
            $('#editColourForm').attr('action', "{{ route('colour.update', '') }}/" + id);
        });
    });
</script>
@endpush
