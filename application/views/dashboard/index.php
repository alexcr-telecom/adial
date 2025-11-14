<div class="alert-container"></div>

<h1 class="h2 mb-4"><?php echo $this->lang->line('dashboard_title'); ?></h1>

<!-- System Status Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted"><?php echo $this->lang->line('dashboard_asterisk_status'); ?></h6>
                        <span class="status-badge status-<?php echo $asterisk_status; ?>" id="asterisk-status">
                            <?php echo strtoupper($asterisk_status); ?>
                        </span>
                    </div>
                    <i class="fas fa-server fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted"><?php echo $this->lang->line('dashboard_database_status'); ?></h6>
                        <span class="status-badge status-<?php echo $database_status; ?>" id="database-status">
                            <?php echo strtoupper($database_status); ?>
                        </span>
                    </div>
                    <i class="fas fa-database fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted"><?php echo $this->lang->line('dashboard_ari_websocket'); ?></h6>
                        <span class="status-badge status-<?php echo $ari_ws_status; ?>" id="ari-ws-status">
                            <?php echo strtoupper($ari_ws_status); ?>
                        </span>
                    </div>
                    <i class="fas fa-plug fa-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted"><?php echo $this->lang->line('dashboard_active_channels'); ?></h6>
                        <h3 class="mb-0" id="active-channels"><?php echo $active_channels; ?></h3>
                    </div>
                    <i class="fas fa-phone fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?php echo $this->lang->line('dashboard_total_campaigns'); ?></h6>
                <h3><?php echo count($campaigns); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?php echo $this->lang->line('dashboard_active_campaigns'); ?></h6>
                <h3><?php echo count($active_campaigns); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?php echo $this->lang->line('dashboard_today_calls'); ?></h6>
                <h3><?php echo $today_calls; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?php echo $this->lang->line('dashboard_today_answered'); ?></h6>
                <h3><?php echo $today_answered; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Campaigns List -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $this->lang->line('dashboard_campaigns'); ?></h5>
                <a href="<?php echo base_url('campaigns/add'); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> <?php echo $this->lang->line('dashboard_new_campaign'); ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($campaigns)): ?>
                    <p class="text-center text-muted"><?php echo $this->lang->line('dashboard_no_campaigns'); ?> <a href="<?php echo base_url('campaigns/add'); ?>"><?php echo $this->lang->line('dashboard_create_one_now'); ?></a></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('name'); ?></th>
                                    <th><?php echo $this->lang->line('status'); ?></th>
                                    <th><?php echo $this->lang->line('dashboard_concurrent_calls'); ?></th>
                                    <th><?php echo $this->lang->line('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($campaign->name); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($campaign->description); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $campaign->status; ?>">
                                                <?php echo strtoupper($campaign->status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $campaign->concurrent_calls; ?></td>
                                        <td>
                                            <a href="<?php echo base_url('campaigns/view/' . $campaign->id); ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo base_url('campaigns/edit/' . $campaign->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Active Channels -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $this->lang->line('dashboard_active_channels'); ?></h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div id="channels-list">
                    <?php if (empty($channels_list)): ?>
                        <p class="text-center text-muted"><?php echo $this->lang->line('dashboard_no_active_channels'); ?></p>
                    <?php else: ?>
                        <?php foreach ($channels_list as $channel): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <strong><?php echo isset($channel['name']) ? htmlspecialchars($channel['name']) : 'N/A'; ?></strong><br>
                                <small class="text-muted">
                                    <?php echo $this->lang->line('dashboard_channel_state'); ?>: <?php echo isset($channel['state']) ? htmlspecialchars($channel['state']) : 'N/A'; ?><br>
                                    <?php if (isset($channel['caller']['number'])): ?>
                                        <?php echo $this->lang->line('dashboard_channel_caller'); ?>: <?php echo htmlspecialchars($channel['caller']['number']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-refresh status every 5 seconds
    setInterval(function() {
        $.get(base_url + 'dashboard/get_status', function(data) {
            $('#asterisk-status').removeClass('status-online status-offline').addClass('status-' + data.asterisk).text(data.asterisk.toUpperCase());
            $('#database-status').removeClass('status-online status-offline').addClass('status-' + data.database).text(data.database.toUpperCase());
            $('#ari-ws-status').removeClass('status-online status-offline').addClass('status-' + data.asterisk).text(data.asterisk.toUpperCase());
            $('#active-channels').text(data.active_channels);
        });

        // Refresh channels list
        $.get(base_url + 'dashboard/get_channels', function(data) {
            if (data.success && data.channels.length > 0) {
                var html = '';
                data.channels.forEach(function(channel) {
                    html += '<div class="border-bottom pb-2 mb-2">';
                    html += '<strong>' + (channel.name || 'N/A') + '</strong><br>';
                    html += '<small class="text-muted">';
                    html += '<?php echo $this->lang->line('dashboard_channel_state'); ?>: ' + (channel.state || 'N/A');
                    if (channel.caller && channel.caller.number) {
                        html += '<br><?php echo $this->lang->line('dashboard_channel_caller'); ?>: ' + channel.caller.number;
                    }
                    html += '</small></div>';
                });
                $('#channels-list').html(html);
            } else {
                $('#channels-list').html('<p class="text-center text-muted"><?php echo $this->lang->line('dashboard_no_active_channels'); ?></p>');
            }
        });
    }, 5000);
</script>
