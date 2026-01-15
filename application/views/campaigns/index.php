<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?php echo $this->lang->line('campaigns_title'); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo site_url('campaigns/add'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo $this->lang->line('campaigns_new'); ?>
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
                <table class="table table-striped table-hover" id="campaignsTable">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('campaigns_id'); ?></th>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('status'); ?></th>
                            <th><?php echo $this->lang->line('campaigns_total_numbers'); ?></th>
                            <th><?php echo $this->lang->line('campaigns_pending'); ?></th>
                            <th><?php echo $this->lang->line('campaigns_completed'); ?></th>
                            <th><?php echo $this->lang->line('campaigns_concurrent_calls'); ?></th>
                            <th><?php echo $this->lang->line('created'); ?></th>
                            <th><?php echo $this->lang->line('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($campaigns)): ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td><?php echo $campaign->id; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('campaigns/view/'.$campaign->id); ?>">
                                            <strong><?php echo htmlspecialchars($campaign->name); ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = array(
                                            'running' => 'success',
                                            'stopped' => 'secondary',
                                            'paused' => 'warning'
                                        );
                                        $class = isset($status_class[$campaign->status]) ? $status_class[$campaign->status] : 'secondary';
                                        ?>
                                        <span class="badge badge-<?php echo $class; ?>" id="status-<?php echo $campaign->id; ?>">
                                            <?php echo ucfirst($campaign->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo isset($campaign->stats->total) ? $campaign->stats->total : 0; ?></td>
                                    <td><?php echo isset($campaign->stats->pending) ? $campaign->stats->pending : 0; ?></td>
                                    <td><?php echo isset($campaign->stats->completed) ? $campaign->stats->completed : 0; ?></td>
                                    <td><?php echo $campaign->concurrent_calls; ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($campaign->created_at)); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Start/Play button - shown when stopped or paused -->
                                            <button type="button" class="btn btn-success btn-control btn-start-<?php echo $campaign->id; ?>"
                                                    data-id="<?php echo $campaign->id; ?>"
                                                    data-action="start"
                                                    <?php echo ($campaign->status == 'running') ? 'style="display:none;"' : ''; ?>>
                                                <i class="fas fa-play"></i>
                                            </button>

                                            <!-- Pause button - shown when running -->
                                            <button type="button" class="btn btn-warning btn-control btn-pause-<?php echo $campaign->id; ?>"
                                                    data-id="<?php echo $campaign->id; ?>"
                                                    data-action="pause"
                                                    <?php echo ($campaign->status != 'running') ? 'style="display:none;"' : ''; ?>>
                                                <i class="fas fa-pause"></i>
                                            </button>

                                            <!-- Stop button -->
                                            <button type="button" class="btn btn-danger btn-control"
                                                    data-id="<?php echo $campaign->id; ?>"
                                                    data-action="stop">
                                                <i class="fas fa-stop"></i>
                                            </button>

                                            <!-- View button -->
                                            <a href="<?php echo site_url('campaigns/view/'.$campaign->id); ?>"
                                               class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- Edit button -->
                                            <a href="<?php echo site_url('campaigns/edit/'.$campaign->id); ?>"
                                               class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Delete button -->
                                            <?php if ($campaign->status == 'stopped'): ?>
                                                <button type="button" class="btn btn-danger btn-delete"
                                                        data-id="<?php echo $campaign->id; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center"><?php echo $this->lang->line('campaigns_no_campaigns'); ?></td>
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
    // Initialize DataTable
    $('#campaignsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25
    });

    // Campaign control buttons
    $('.btn-control').click(function() {
        var campaignId = $(this).data('id');
        var action = $(this).data('action');
        var btn = $(this);

        $.ajax({
            url: '<?php echo site_url('campaigns/control'); ?>/' + campaignId + '/' + action,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update button visibility and status badge
                    var statusBadge = $('#status-' + campaignId);
                    var startBtn = $('.btn-start-' + campaignId);
                    var pauseBtn = $('.btn-pause-' + campaignId);

                    if (action == 'start') {
                        // Started - show pause, hide start
                        startBtn.hide();
                        pauseBtn.show();
                        statusBadge.removeClass('badge-secondary badge-warning').addClass('badge-success').text('Running');
                    } else if (action == 'pause') {
                        // Paused - show start, hide pause
                        pauseBtn.hide();
                        startBtn.show();
                        statusBadge.removeClass('badge-success badge-secondary').addClass('badge-warning').text('Paused');
                    } else if (action == 'stop') {
                        // Stopped - show start, hide pause
                        pauseBtn.hide();
                        startBtn.show();
                        statusBadge.removeClass('badge-success badge-warning').addClass('badge-secondary').text('Stopped');
                    }
                } else {
                    alert('<?php echo $this->lang->line('campaigns_error'); ?>: ' + response.message);
                }
            },
            error: function() {
                alert('<?php echo $this->lang->line('campaigns_failed_control'); ?>');
            }
        });
    });

    // Delete campaign
    $('.btn-delete').click(function() {
        if (confirm('<?php echo $this->lang->line('campaigns_confirm_delete'); ?>')) {
            var campaignId = $(this).data('id');
            window.location.href = '<?php echo site_url('campaigns/delete'); ?>/' + campaignId;
        }
    });
});
</script>
