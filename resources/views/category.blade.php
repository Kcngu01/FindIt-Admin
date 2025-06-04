@extends('layouts.app')

@section('title', 'Category')

@section('content')
    <div class="container">
        <h1>Category</h1>
        <div class="d-flex justify-content-end">
            <button type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="btn btn-secondary mt-3 float-end">
                <i class="bi bi-plus"></i>Add Category
            </button> 
        </div>

        <!-- Add Category Modal -->
        <div class="modal fade" id="addCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" arial-label="Close"></button>
                    </div>
                    <form action="{{route('category.store')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="name" required>
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

        <!-- Edit category modal -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    </div>
                    <form id="editCategoryForm" action="" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="editCategoryName" name="name" required>
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
                <h5 class="card-title">Category List</h5>
                @if(count($categories)>0)
                <div class="mb-3 search-bar d-inline-block float-end">
                    <!-- <div class="input-group"> -->
                        <input type="text" class="form-control" id="searchCategory" placeholder="ID/Name">
                    <!-- </div> -->
                </div>
                <table id="categoryTable" class="table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Category ID</th>
                            <th>Category Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td style="width:20%; white-space: no-wrap;">
                                <div class="d-inline-flex">
                                    <form action="{{route('category.destroy', $category->id)}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" class="me-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                                    </form>
                                    <button type="button" class="btn btn-outline-secondary edit-category-btn" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal"
                                            data-id="{{$category->id}}"
                                            data-name="{{$category->name}}">Edit
                                    </button>
                                </div>
                            </td>
                            <td>{{$category->id}}</td>
                            <td>{{$category->name}}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No categories found</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
                @else
                <div class="alert alert-info mt-3">
                    <p class="mb-0">No categories found. Please add a new category using the "Add Category" button above.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="module">
    $(document).ready(function(){
        var table= $('#categoryTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5,10,20,50],
            "dom": 'lrtip',
            "columnDefs": [
                { "targets": '_all', "className": 'dt-left' } // Apply to all columns
            ]
        });

        $('#searchCategory').on('keyup', function(){
            table.search($(this).val()).draw();
        });

        $('.edit-category-btn').on('click',function(){
            const id= $(this).data('id');
            const name= $(this).data('name');
            $('#editCategoryName').val(name);
            $('#editCategoryForm').attr('action', "{{ route('category.update', '') }}/" + id);
        });
    });
</script>
@endpush

