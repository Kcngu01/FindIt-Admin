@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Claim Approval History</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Claim Approval List</h5>
            <div class="mb-3 search-bar d-inline-block float-end"> 
                <div class="input-group">
                    <input type="text" id="searchClaim" class="form-control" placeholder="ID/Matric no./Status">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <table id="claimTable" class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Claim ID</th>
                        <th>Found Item ID</th>
                        <th>Claimant Matric no.</th>
                        <th>Admin ID</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($claims as $claim)
                    <tr>
                        <form method="GET" action="{{ route('claim-history.view', $claim->id) }}">
                            @csrf
                            <td><button type="submit" class="btn btn-outline-secondary" data-id="{{$claim->id}}">View</button></td>  
                        </form>
                        <td>{{$claim->id}}</td>
                        <td>{{$claim->found_item_id}}</td>
                        <td>{{$claim->student->matric_no}}</td>
                        <td>{{$claim->admin_id}}</td>
                        <td>{{$claim->status}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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

        $('#searchClaim').on('keyup',function(){
            table.search($(this).val()).draw();
        })

        $('#searchButton').on('click',function(){
            table.search($('#searchClaim').val()).draw();
        });
        
    });
</script>
@endpush