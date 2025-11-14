<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>System Settings</h2>
        </div>
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-info" id="testAriBtn">
                <i class="fas fa-plug"></i> Test ARI Connection
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="30%">Setting</th>
                                    <th width="30%">Value</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($settings)): ?>
                                    <?php foreach ($settings as $setting): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($setting->setting_key); ?></strong>
                                            </td>
                                            <td>
                                                <?php if (in_array($setting->setting_key, array('ari_enabled', 'debug_mode'))): ?>
                                                    <select class="form-control" name="<?php echo htmlspecialchars($setting->setting_key); ?>">
                                                        <option value="1" <?php echo $setting->setting_value == '1' ? 'selected' : ''; ?>>Enabled</option>
                                                        <option value="0" <?php echo $setting->setting_value == '0' ? 'selected' : ''; ?>>Disabled</option>
                                                    </select>
                                                <?php else: ?>
                                                    <input type="text" class="form-control"
                                                           name="<?php echo htmlspecialchars($setting->setting_key); ?>"
                                                           value="<?php echo htmlspecialchars($setting->setting_value); ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($setting->description); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No settings found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- User Management Section (Admin Only) -->
            <?php if ($this->auth->is_admin()): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5>
                        User Management
                        <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user->username); ?></td>
                                            <td><?php echo htmlspecialchars($user->full_name); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $user->role == 'admin' ? 'warning' : 'info'; ?>">
                                                    <?php echo ucfirst($user->role); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $user->is_active ? 'success' : 'secondary'; ?>">
                                                    <?php echo $user->is_active ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $user->last_login ? date('Y-m-d H:i', strtotime($user->last_login)) : 'Never'; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary btn-edit-user"
                                                        data-id="<?php echo $user->id; ?>"
                                                        data-username="<?php echo htmlspecialchars($user->username); ?>"
                                                        data-email="<?php echo htmlspecialchars($user->email); ?>"
                                                        data-full-name="<?php echo htmlspecialchars($user->full_name); ?>"
                                                        data-role="<?php echo $user->role; ?>"
                                                        data-is-active="<?php echo $user->is_active; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user->id != $this->auth->user_id()): ?>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-user"
                                                            data-id="<?php echo $user->id; ?>"
                                                            data-username="<?php echo htmlspecialchars($user->username); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>System Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>PHP Version:</th>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <th>CodeIgniter:</th>
                            <td><?php echo CI_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th>Database:</th>
                            <td><?php echo $this->db->database; ?></td>
                        </tr>
                        <tr>
                            <th>Server:</th>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="<?php echo site_url('campaigns'); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-bullhorn"></i> Manage Campaigns
                        </a>
                        <a href="<?php echo site_url('cdr'); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-phone-alt"></i> View Call Records
                        </a>
                        <a href="<?php echo site_url('ivr'); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-sitemap"></i> Manage IVR Menus
                        </a>
                        <a href="<?php echo site_url('monitoring'); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line"></i> System Monitoring
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_username">Username *</label>
                        <input type="text" class="form-control" id="add_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="add_password">Password *</label>
                        <input type="password" class="form-control" id="add_password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="add_full_name">Full Name</label>
                        <input type="text" class="form-control" id="add_full_name" name="full_name">
                    </div>
                    <div class="form-group">
                        <label for="add_email">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="add_role">Role</label>
                        <select class="form-control" id="add_role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_username">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit_full_name">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select class="form-control" id="edit_role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_active">Status</label>
                        <select class="form-control" id="edit_is_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Test ARI connection
    $('#testAriBtn').click(function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');

        $.ajax({
            url: '<?php echo site_url('settings/test_ari'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✓ ARI Connection Successful!\n\nAsterisk Version: ' + (response.data && response.data.system ? response.data.system.version : 'Unknown'));
                } else {
                    alert('✗ ARI Connection Failed\n\n' + response.message);
                }
                $btn.prop('disabled', false).html('<i class="fas fa-plug"></i> Test ARI Connection');
            },
            error: function() {
                alert('✗ Failed to test ARI connection');
                $btn.prop('disabled', false).html('<i class="fas fa-plug"></i> Test ARI Connection');
            }
        });
    });

    // Add User
    $('#addUserForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?php echo site_url('settings/add_user'); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + response.message);
                    location.reload();
                } else {
                    alert('✗ ' + response.message);
                }
            },
            error: function() {
                alert('✗ Failed to add user');
            }
        });
    });

    // Edit User - Load data
    $('.btn-edit-user').click(function() {
        var userId = $(this).data('id');
        var username = $(this).data('username');
        var email = $(this).data('email');
        var fullName = $(this).data('full-name');
        var role = $(this).data('role');
        var isActive = $(this).data('is-active');

        $('#edit_user_id').val(userId);
        $('#edit_username').val(username);
        $('#edit_email').val(email);
        $('#edit_full_name').val(fullName);
        $('#edit_role').val(role);
        $('#edit_is_active').val(isActive);
        $('#edit_password').val('');

        $('#editUserModal').modal('show');
    });

    // Update User
    $('#editUserForm').submit(function(e) {
        e.preventDefault();

        var userId = $('#edit_user_id').val();

        $.ajax({
            url: '<?php echo site_url('settings/update_user'); ?>/' + userId,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + response.message);
                    location.reload();
                } else {
                    alert('✗ ' + response.message);
                }
            },
            error: function() {
                alert('✗ Failed to update user');
            }
        });
    });

    // Delete User
    $('.btn-delete-user').click(function() {
        var userId = $(this).data('id');
        var username = $(this).data('username');

        if (confirm('Are you sure you want to delete user "' + username + '"?')) {
            $.ajax({
                url: '<?php echo site_url('settings/delete_user'); ?>/' + userId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✓ ' + response.message);
                        location.reload();
                    } else {
                        alert('✗ ' + response.message);
                    }
                },
                error: function() {
                    alert('✗ Failed to delete user');
                }
            });
        }
    });
});
</script>
