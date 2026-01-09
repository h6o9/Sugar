@extends('admin.layout.app')
@section('title', 'Notifications')

@section('content')
<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Notifications</h4>
                        </div>
                        <div class="card-body table-striped table-bordered table-responsive">
                            @if (Auth::guard('admin')->check() ||
                            ($sideMenuPermissions->has('Notifications') && $sideMenuPermissions['Notifications']->contains('create')))
                            <a class="btn mb-3 text-white" data-bs-toggle="modal" style="background-color: #cb84fe;"
                                data-bs-target="#createUserModal">Create</a>
                            @endif

                            @if (Auth::guard('admin')->check() ||
                            ($sideMenuPermissions->has('Notifications') && $sideMenuPermissions['Notifications']->contains('delete')))
                            <form action="{{ route('notifications.deleteAll') }}" method="POST"
                                class="d-inline-block float-right">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-primary mb-3 delete_all">
                                    Delete All
                                </button>
                            </form>
                            @endif
                            <table class="table" id="table_id_events">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notifications as $notification)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $notification->title }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($notification->description), 150, '...') }}
                                        </td>
                                        <td>{{ $notification->created_at->format('d M Y') }}</td>
                                        <td>
                                            @if (Auth::guard('admin')->check() ||
                                            ($sideMenuPermissions->has('Notifications') && $sideMenuPermissions['Notifications']->contains('delete')))
                                            <form id="delete-form-{{ $notification->id }}"
                                                action="{{ route('notification.destroy', $notification->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <button class="show_confirm btn d-flex "
                                                style="background-color: #cb84fe;"
                                                data-form="delete-form-{{ $notification->id }}" type="button">
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                            @endif


                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- /.card-body -->
                    </div> <!-- /.card -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->
        </div> <!-- /.section-body -->
    </section>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createUserForm" method="POST" action="{{ route('notification.store') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_type" value="user">

                    <div class="form-group" id="user_field">
                        <label><strong>Sellers <span style="color: red;">*</span></strong></label>
                        <div class="form-check mb-2">
                            <input type="checkbox" id="select_all_users" class="form-check-input">
                            <label class="form-check-label" for="select_all_users">Select All</label>
                        </div>
                        <select name="users[]" id="users" class="form-control select2" multiple>
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('users') && in_array($user->id, old('users')) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('users')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label><strong>Title <span style="color:red;">*</span></strong></label>
                        <input type="text" name="title" class="form-control" placeholder="Title" required>
                        @error('title')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><strong>Description <span style="color:red;">*</span></strong></label>
                        <textarea name="description" class="form-control" placeholder="Type your message here..." rows="4" required></textarea>
                        @error('description')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="createBtn">
                        <span id="createBtnText">Create Notification</span>
                        <span id="createSpinner" style="display: none;">
                            <i class="fa fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

<!-- ✅ ADD THIS LINE -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    // ✅ DataTable
    $('#table_id_events').DataTable();

    // ✅ Select2 INIT — ONLY ONCE
    $('#users').select2({
        dropdownParent: $('#createUserModal'),
        placeholder: "Select sellers",
        allowClear: true,
        width: '100%'
    });

    // =============================
    // ✅ SELECT ALL FIXED WORKING
    // =============================
    $('#select_all_users').on('change', function () {
        let $usersSelect = $('#users');
        let allValues = [];
        
        // Get all option values
        $('#users option').each(function () {
            allValues.push($(this).val());
        });

        if ($(this).is(':checked')) {
            // Set all values and update Select2
            $usersSelect.val(allValues).trigger('change');
        } else {
            // Clear all selections
            $usersSelect.val(null).trigger('change');
        }
    });

    // =============================
    // ✅ AUTO UNCHECK SELECT ALL
    // =============================
    $('#users').on('change', function () {
        let total = $('#users option').length;
        let selected = $(this).val() ? $(this).val().length : 0;

        // Update Select All checkbox
        $('#select_all_users').prop('checked', total === selected);
        
        // Clear any existing custom errors
        $('#user_field .custom-error').remove();
    });

    // =============================
    // ✅ MODAL RESET (cross icon fix)
    // =============================
    $('#createUserModal').on('hidden.bs.modal', function () {
        $('#createUserForm')[0].reset();
        $('#users').val(null).trigger('change');
        $('#select_all_users').prop('checked', false);
        $('#user_field .custom-error').remove();
    });

    // =============================
    // ✅ FORM VALIDATION
    // =============================
    $('#createUserForm').on('submit', function (e) {
        let selectedUsers = $('#users').val();
        let selectAll = $('#select_all_users').is(':checked');

        // Clear previous errors
        $('#user_field .custom-error').remove();

        // If neither select all nor any user is selected
        if (!selectAll && (!selectedUsers || selectedUsers.length === 0)) {
            e.preventDefault();
            
            // Add custom error message
            $('#user_field').append(
                '<div class="text-danger custom-error mt-1">Please select at least one seller or check "Select All".</div>'
            );
            
            return false;
        }

        // Show loading spinner
        $('#createSpinner').show();
        $('#createBtnText').hide();
        $('#createBtn').prop('disabled', true);
    });

});
</script>

@endsection