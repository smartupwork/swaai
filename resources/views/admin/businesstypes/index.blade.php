@extends('admin.layout')
@section('admin-buisnesstype-index-content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <div class="container-xxl">
        @if (Session::get('success'))
            <div class=" alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif
        @if (Session::get('error'))
            <div class="alert alert-danger">
                {{ Session::get('error') }}
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Business TYpes</h4>
                                <a href="{{ route('businesstypes.create') }}">
                                    <button type="button" class="btn btn-info">Create Business Type</button>
                                </a>
                            </div><!--end col-->
                        </div> <!--end row-->
                    </div><!--end card-header-->
                    <div class="card-body pt-0">

                        <div class="table-responsive">
                            <table class="table datatable" id="user-index-table">
                                <thead>
                                    <tr>

                                        <th>Title</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($buisnesstypes as $type)
                                        <tr>
                                            <td>
                                                {{ $type->title }}
                                            </td>
                                            <td>{{ $type->created_at }}</td>
                                            <td>
                                                <a href="{{ route('businesstypes.edit', $type->id) }}"
                                                    class="btn btn-primary btn-sm"
                                                    style="height:30px;width:30px;border-radius:50%" title="edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('businesstypes.destroy', [$type->id]) }}"
                                                    class="d-inline-block individualDeleteForm">
                                                    @csrf
                                                    <button type="button" class="btn btn-danger btn-sm dltBtn"
                                                        data-id="{{ $type->id }}"
                                                        style="height:30px;width:30px;border-radius:50%" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        $('#user-index-table').DataTable({
            pageLength: 25
        });

        $(document).ready(function() {

            $('.dltBtn').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, this business type cannot be recovered!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
