@extends('admin.layout')
@section('admin-user-index-content')
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
                                <h4 class="card-title">Users</h4>
                                {{-- <a href="{{ route('users.create') }}">
                                    <button type="button" class="btn btn-info">Create User</button>
                                </a> --}}
                            </div><!--end col-->
                        </div> <!--end row-->
                    </div><!--end card-header-->
                    <div class="card-body pt-0">

                        <div class="table-responsive">
                            <table class="table datatable" id="user-index-table">
                                <thead>
                                    <tr>

                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>
                                                {{ $user->first_name && $user->last_name ? $user->first_name . ' ' . $user->last_name : 'N/A' }}
                                            </td>

                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if ($user->role_id == '1')
                                                    business
                                                @elseif($user->role_id == '2')
                                                    consumer
                                                @elseif($user->role_id == '3')
                                                    admin
                                                @endif
                                            </td>

                                            <td>{{ $user->created_at }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('users.destroy', [$user->id]) }}"
                                                    class="d-inline-block individualDeleteForm">
                                                    @csrf
                                                    <button type="button" class="btn btn-danger btn-sm dltBtn"
                                                        data-id="{{ $user->id }}"
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
                    text: "Once deleted, this user cannot be recovered!",
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
