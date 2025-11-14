<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?php echo $this->lang->line('ivr_title'); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo site_url('ivr/add'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo $this->lang->line('ivr_new'); ?>
            </a>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('ivr_id'); ?></th>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('ivr_campaign'); ?></th>
                            <th><?php echo $this->lang->line('ivr_audio_file'); ?></th>
                            <th><?php echo $this->lang->line('ivr_timeout'); ?></th>
                            <th><?php echo $this->lang->line('ivr_max_digits'); ?></th>
                            <th><?php echo $this->lang->line('ivr_actions'); ?></th>
                            <th><?php echo $this->lang->line('created'); ?></th>
                            <th><?php echo $this->lang->line('ivr_operations'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ivr_menus)): ?>
                            <?php foreach ($ivr_menus as $menu): ?>
                                <tr>
                                    <td><?php echo $menu->id; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('ivr/view/'.$menu->id); ?>">
                                            <strong><?php echo htmlspecialchars($menu->name); ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('campaigns/view/'.$menu->campaign_id); ?>">
                                            Campaign #<?php echo $menu->campaign_id; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($menu->audio_file); ?></small>
                                    </td>
                                    <td><?php echo $menu->timeout; ?>s</td>
                                    <td><?php echo $menu->max_digits; ?></td>
                                    <td>
                                        <?php
                                        // Get action count
                                        $this->load->model('Ivr_action_model');
                                        $actions = $this->Ivr_action_model->get_by_menu($menu->id);
                                        echo count($actions);
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($menu->created_at)); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo site_url('ivr/view/'.$menu->id); ?>"
                                               class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo site_url('ivr/edit/'.$menu->id); ?>"
                                               class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-delete"
                                                    data-id="<?php echo $menu->id; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <?php echo $this->lang->line('ivr_no_menus'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete IVR menu
    $('.btn-delete').click(function() {
        if (confirm('<?php echo $this->lang->line('ivr_confirm_delete'); ?>')) {
            var menuId = $(this).data('id');
            window.location.href = '<?php echo site_url('ivr/delete'); ?>/' + menuId;
        }
    });
});
</script>
