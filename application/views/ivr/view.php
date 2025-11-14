<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>IVR Menu: <?php echo htmlspecialchars($ivr_menu->name); ?></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo site_url('ivr/edit/'.$ivr_menu->id); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> <?php echo $this->lang->line('btn_edit'); ?>
            </a>
            <a href="<?php echo site_url('ivr'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo $this->lang->line('btn_back'); ?>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $this->lang->line('ivr_menu_details'); ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">ID</th>
                            <td><?php echo $ivr_menu->id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <td><?php echo htmlspecialchars($ivr_menu->name); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('ivr_campaign'); ?></th>
                            <td>
                                <?php if ($campaign): ?>
                                    <a href="<?php echo site_url('campaigns/view/'.$campaign->id); ?>">
                                        <?php echo htmlspecialchars($campaign->name); ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('ivr_audio_file'); ?></th>
                            <td>
                                <?php echo htmlspecialchars($ivr_menu->audio_file); ?>
                                <br>
                                <button type="button" class="btn btn-sm btn-success mt-2" id="playAudioBtn">
                                    <i class="fas fa-play"></i> <?php echo $this->lang->line('ivr_play_audio'); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-info mt-2 ml-2" id="downloadAudioBtn">
                                    <i class="fas fa-download"></i> <?php echo $this->lang->line('btn_download'); ?>
                                </button>
                                <audio id="audioPlayer" style="display:none;" controls>
                                    <source src="<?php echo site_url('ivr/audio/'.$ivr_menu->audio_file); ?>" type="audio/wav">
                                    <?php echo $this->lang->line('ivr_audio_not_supported'); ?>
                                </audio>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('ivr_timeout'); ?></th>
                            <td><?php echo $ivr_menu->timeout; ?> <?php echo $this->lang->line('ivr_seconds'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('ivr_max_digits'); ?></th>
                            <td><?php echo $ivr_menu->max_digits; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('created'); ?></th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($ivr_menu->created_at)); ?></td>
                        </tr>
                        <?php if ($ivr_menu->updated_at): ?>
                        <tr>
                            <th><?php echo $this->lang->line('updated'); ?></th>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($ivr_menu->updated_at)); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $this->lang->line('ivr_actions'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($ivr_menu->actions)): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('ivr_dtmf_digit'); ?></th>
                                    <th><?php echo $this->lang->line('ivr_action_type'); ?></th>
                                    <th><?php echo $this->lang->line('ivr_action_value'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ivr_menu->actions as $action): ?>
                                    <?php
                                    // Display special labels for i and t
                                    $digit_label = $action->dtmf_digit;
                                    if ($action->dtmf_digit === 'i') {
                                        $digit_label = $this->lang->line('ivr_dtmf_invalid');
                                    } elseif ($action->dtmf_digit === 't') {
                                        $digit_label = $this->lang->line('ivr_dtmf_timeout');
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?php echo ($action->dtmf_digit === 'i' || $action->dtmf_digit === 't') ? 'warning' : 'primary'; ?>">
                                                <?php echo htmlspecialchars($digit_label); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $action_types = array(
                                                'exten' => $this->lang->line('ivr_action_call_extension'),
                                                'queue' => $this->lang->line('ivr_action_queue'),
                                                'hangup' => $this->lang->line('ivr_action_hangup'),
                                                'playback' => $this->lang->line('ivr_action_playback'),
                                                'goto_ivr' => $this->lang->line('ivr_action_goto_ivr')
                                            );
                                            echo isset($action_types[$action->action_type]) ? $action_types[$action->action_type] : ucfirst($action->action_type);
                                            ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($action->action_value); ?></code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center"><?php echo $this->lang->line('ivr_no_actions'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var audioPath = '<?php echo site_url('ivr/audio/'.$ivr_menu->audio_file); ?>';

    $('#playAudioBtn').click(function() {
        var audio = document.getElementById('audioPlayer');
        var btn = $(this);

        if (audio.paused) {
            audio.play();
            btn.html('<i class="fas fa-pause"></i> <?php echo $this->lang->line('ivr_pause'); ?>');
            btn.removeClass('btn-success').addClass('btn-warning');

            // Show audio player controls
            $('#audioPlayer').show();
        } else {
            audio.pause();
            btn.html('<i class="fas fa-play"></i> <?php echo $this->lang->line('ivr_play_audio'); ?>');
            btn.removeClass('btn-warning').addClass('btn-success');
        }
    });

    // Download button
    $('#downloadAudioBtn').click(function() {
        window.location.href = audioPath;
    });

    // Reset button when audio ends
    $('#audioPlayer').on('ended', function() {
        $('#playAudioBtn')
            .html('<i class="fas fa-play"></i> <?php echo $this->lang->line('ivr_play_audio'); ?>')
            .removeClass('btn-warning')
            .addClass('btn-success');
    });
});
</script>
