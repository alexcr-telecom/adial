<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?php echo $this->lang->line('monitoring_realtime'); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <span class="badge badge-success" id="updateStatus">
                <i class="fas fa-circle"></i> <?php echo $this->lang->line('monitoring_auto_updating'); ?>
            </span>
        </div>
    </div>

    <!-- Today's Stats -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0" id="totalCalls"><?php echo $today_stats['total_calls']; ?></h3>
                    <small class="text-muted"><?php echo $this->lang->line('monitoring_total_calls_today'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-success" id="answeredCalls"><?php echo $today_stats['answered_calls']; ?></h3>
                    <small class="text-muted"><?php echo $this->lang->line('monitoring_answered'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-info" id="answerRate"><?php echo $today_stats['answer_rate']; ?>%</h3>
                    <small class="text-muted"><?php echo $this->lang->line('monitoring_answer_rate'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-primary" id="avgTalkTime"><?php echo gmdate('H:i:s', $today_stats['avg_talk_time']); ?></h3>
                    <small class="text-muted"><?php echo $this->lang->line('monitoring_avg_talk_time'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Campaigns -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bullhorn"></i> <?php echo $this->lang->line('monitoring_active_campaigns'); ?></h5>
                </div>
                <div class="card-body">
                    <div id="activeCampaigns">
                        <?php if (!empty($active_campaigns)): ?>
                            <?php foreach ($active_campaigns as $campaign): ?>
                                <div class="campaign-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="<?php echo site_url('campaigns/view/'.$campaign->id); ?>">
                                                    <?php echo htmlspecialchars($campaign->name); ?>
                                                </a>
                                            </h6>
                                            <span class="badge badge-<?php echo $campaign->status == 'running' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($campaign->status); ?>
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted"><?php echo $this->lang->line('monitoring_concurrent'); ?>: <?php echo $campaign->concurrent_calls; ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center"><?php echo $this->lang->line('monitoring_no_active_campaigns'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Channels -->
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-phone"></i> <?php echo $this->lang->line('monitoring_active_calls'); ?>
                        <span class="badge badge-primary float-right" id="channelCount">
                            <?php echo count($active_channels); ?>
                        </span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="activeChannels">
                        <?php if (!empty($active_channels)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('monitoring_channel_id'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_state'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_caller'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_connected'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($active_channels as $channel): ?>
                                            <tr>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($channel['id'], 0, 20)); ?>...</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?php echo htmlspecialchars($channel['state']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo isset($channel['caller']['number']) ? htmlspecialchars($channel['caller']['number']) : 'N/A'; ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo isset($channel['connected']['number']) ? htmlspecialchars($channel['connected']['number']) : 'N/A'; ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center"><?php echo $this->lang->line('monitoring_no_active_channels'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> <?php echo $this->lang->line('monitoring_campaign_statistics'); ?></h5>
                </div>
                <div class="card-body">
                    <div id="campaignStats">
                        <?php if (!empty($active_campaigns)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('monitoring_campaign'); ?></th>
                                            <th><?php echo $this->lang->line('status'); ?></th>
                                            <th><?php echo $this->lang->line('total'); ?></th>
                                            <th><?php echo $this->lang->line('campaigns_pending'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_calling'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_answered'); ?></th>
                                            <th><?php echo $this->lang->line('campaigns_completed'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_failed'); ?></th>
                                            <th><?php echo $this->lang->line('monitoring_progress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="campaignStatsBody">
                                        <!-- Will be updated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center"><?php echo $this->lang->line('monitoring_no_active_campaigns_stats'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var updateInterval;

$(document).ready(function() {
    // Initial load
    updateData();

    // Auto-update every 3 seconds
    updateInterval = setInterval(updateData, 3000);
});

function updateData() {
    $.ajax({
        url: '<?php echo site_url('monitoring/get_realtime_data'); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update today's stats
            $('#totalCalls').text(data.today_stats.total_calls);
            $('#answeredCalls').text(data.today_stats.answered_calls);
            $('#answerRate').text(data.today_stats.answer_rate + '%');
            $('#avgTalkTime').text(formatTime(data.today_stats.avg_talk_time));

            // Update channel count
            $('#channelCount').text(data.channel_count);

            // Update active channels
            updateChannels(data.channels);

            // Update campaign stats
            updateCampaignStats(data.campaigns);

            // Update status indicator
            $('#updateStatus').removeClass('badge-danger').addClass('badge-success')
                .html('<i class="fas fa-circle"></i> <?php echo $this->lang->line('monitoring_auto_updating'); ?>');
        },
        error: function() {
            $('#updateStatus').removeClass('badge-success').addClass('badge-danger')
                .html('<i class="fas fa-circle"></i> <?php echo $this->lang->line('monitoring_update_failed'); ?>');
        }
    });
}

function updateChannels(channels) {
    if (channels.length === 0) {
        $('#activeChannels').html('<p class="text-muted text-center"><?php echo $this->lang->line('monitoring_no_active_channels'); ?></p>');
        return;
    }

    var html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th><?php echo $this->lang->line('monitoring_channel_id'); ?></th><th><?php echo $this->lang->line('monitoring_state'); ?></th><th><?php echo $this->lang->line('monitoring_caller'); ?></th><th><?php echo $this->lang->line('monitoring_connected'); ?></th></tr></thead><tbody>';

    channels.forEach(function(channel) {
        html += '<tr>';
        html += '<td><small>' + channel.id.substring(0, 20) + '...</small></td>';
        html += '<td><span class="badge badge-info">' + channel.state + '</span></td>';
        html += '<td><small>' + (channel.caller && channel.caller.number ? channel.caller.number : 'N/A') + '</small></td>';
        html += '<td><small>' + (channel.connected && channel.connected.number ? channel.connected.number : 'N/A') + '</small></td>';
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    $('#activeChannels').html(html);
}

function updateCampaignStats(campaigns) {
    if (campaigns.length === 0) {
        $('#campaignStatsBody').html('<tr><td colspan="9" class="text-center text-muted"><?php echo $this->lang->line('monitoring_no_active_campaigns'); ?></td></tr>');
        return;
    }

    var html = '';

    campaigns.forEach(function(campaign) {
        var stats = campaign.stats;
        var progress = stats.total > 0 ? ((stats.completed / stats.total) * 100).toFixed(2) : 0;

        html += '<tr>';
        html += '<td><a href="<?php echo site_url('campaigns/view'); ?>/' + campaign.id + '">' + campaign.name + '</a></td>';
        html += '<td><span class="badge badge-' + (campaign.status === 'running' ? 'success' : 'warning') + '">' + campaign.status + '</span></td>';
        html += '<td>' + (stats.total || 0) + '</td>';
        html += '<td>' + (stats.pending || 0) + '</td>';
        html += '<td>' + (stats.calling || 0) + '</td>';
        html += '<td>' + (stats.answered || 0) + '</td>';
        html += '<td>' + (stats.completed || 0) + '</td>';
        html += '<td>' + (stats.failed || 0) + '</td>';
        html += '<td><div class="progress"><div class="progress-bar" role="progressbar" style="width: ' + progress + '%">' + progress + '%</div></div></td>';
        html += '</tr>';
    });

    $('#campaignStatsBody').html(html);
}

function formatTime(seconds) {
    var hours = Math.floor(seconds / 3600);
    var minutes = Math.floor((seconds % 3600) / 60);
    var secs = seconds % 60;

    return pad(hours) + ':' + pad(minutes) + ':' + pad(secs);
}

function pad(num) {
    return (num < 10 ? '0' : '') + num;
}
</script>
