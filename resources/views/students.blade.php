@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <h1 class="mb-4">Students</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Student List</h5>
            @if( count($students) > 0 )
            <div class="mb-3 search-bar d-inline-block float-end"> 
                <div class="input-group">
                    <input type="text" id="searchStudent" class="form-control" placeholder="ID/Username/Email">
                    <!-- <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        <i class="bi bi-search"></i>
                    </button> -->
                </div>
            </div>

            <table id="studentTable" class="table">
                <thead>
                    <tr>
                        <!-- <th>Action</th> -->
                        <th>ID</th>
                        <th>Username</th>
                        <th>Matric No.</th>
                        <th>Email Address</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <!-- <form method="POST" action="{{ route('students.destroy', $student->id) }}" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <td><button type="submit" class="btn btn-outline-danger" data-id="{{$student->id}}">Delete</button></td>  
                        </form> -->
                        <td>{{$student->id}}</td>
                        <td>{{$student->name}}</td>
                        <td>{{$student->matric_no}}</td>
                        <td>{{$student->email}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="alert alert-info">No students found</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        
        var table = $('#studentTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5,10,20,50],
            "dom": 'lrtip',
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' } // Apply to all columns
            ]
        });

        $('#searchStudent').on('keyup',function(){
            table.search($(this).val()).draw();
        })

        // $('#searchButton').on('click',function(){
        //     table.search($('#searchStudent').val()).draw();
        // });
        
    });
</script>
@endpush