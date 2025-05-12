@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Claim Review</h1>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Claim Request List</h5>
                @if( count($foundItems) > 0 )
                <div class="mb-3 search-bar d-inline-block float-end">
                    <!-- <div class="input-group"> -->
                        <input type="text" class="form-control" id="searchFoundItem" placeholder="ID/Name">
                    <!-- </div> -->
                </div>
                <table id="foundItemTable" class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Found Item ID</th>
                            <th>Item Name</th>
                            <th>Image</th>
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
                            <td>{{$foundItem->claims_count}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No claim request found</td>
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

        $('#searchFoundItem').on('keyup', function(){
            table.search($(this).val()).draw();
        });

        // $('.edit-location-btn').on('click',function(){
        //     const id= $(this).data('id');
        //     const name= $(this).data('name');
        //     $('#editLocationName').val(name);
        //     $('#editLocationForm').attr('action', "{{ route('location.update', '') }}/" + id);
        // });
    });
</script>
@endpush
