<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo isset($campaign) ? $this->lang->line('campaigns_edit') : $this->lang->line('campaigns_new'); ?></h2>
            <hr>
        </div>
    </div>

    <form method="post" id="campaignForm">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $this->lang->line('campaigns_section_basic'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name"><?php echo $this->lang->line('campaigns_name'); ?> *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo isset($campaign) ? htmlspecialchars($campaign->name) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description"><?php echo $this->lang->line('campaigns_description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($campaign) ? htmlspecialchars($campaign->description) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="concurrent_calls"><?php echo $this->lang->line('campaigns_concurrent_calls'); ?> *</label>
                            <input type="number" class="form-control" id="concurrent_calls" name="concurrent_calls"
                                   value="<?php echo isset($campaign) ? $campaign->concurrent_calls : 1; ?>" min="1" max="100" required>
                            <small class="form-text text-muted"><?php echo $this->lang->line('campaigns_help_max_calls'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="retry_times"><?php echo $this->lang->line('campaigns_retry_times'); ?></label>
                            <input type="number" class="form-control" id="retry_times" name="retry_times"
                                   value="<?php echo isset($campaign) ? $campaign->retry_times : 0; ?>" min="0" max="10">
                        </div>

                        <div class="form-group">
                            <label for="retry_delay"><?php echo $this->lang->line('campaigns_retry_delay'); ?></label>
                            <input type="number" class="form-control" id="retry_delay" name="retry_delay"
                                   value="<?php echo isset($campaign) ? $campaign->retry_delay : 300; ?>" min="60">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $this->lang->line('campaigns_section_trunk'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="trunk_type"><?php echo $this->lang->line('campaigns_trunk_type'); ?> *</label>
                            <select class="form-control" id="trunk_type" name="trunk_type" required>
                                <option value="custom" <?php echo isset($campaign) && $campaign->trunk_type == 'custom' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_trunk_custom'); ?></option>
                                <option value="pjsip" <?php echo isset($campaign) && $campaign->trunk_type == 'pjsip' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_trunk_pjsip'); ?></option>
                                <option value="sip" <?php echo isset($campaign) && $campaign->trunk_type == 'sip' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_trunk_sip'); ?></option>
                            </select>
                        </div>

                        <div class="form-group" id="trunk_value_group">
                            <label for="trunk_value"><?php echo $this->lang->line('campaigns_trunk_value'); ?> *</label>
                            <input type="text" class="form-control" id="trunk_value" name="trunk_value"
                                   value="<?php echo isset($campaign) ? htmlspecialchars($campaign->trunk_value) : ''; ?>"
                                   placeholder="e.g., Local/${EXTEN}@from-internal" required>
                            <small class="form-text text-muted"><?php echo $this->lang->line('campaigns_help_trunk_value'); ?></small>
                        </div>

                        <div class="form-group" id="trunk_select_group" style="display:none;">
                            <label for="trunk_select"><?php echo $this->lang->line('campaigns_select_trunk'); ?> *</label>
                            <select class="form-control" id="trunk_select" name="trunk_select">
                                <option value="">-- <?php echo $this->lang->line('campaigns_select_trunk'); ?> --</option>
                                <?php if (!empty($endpoints)): ?>
                                    <?php foreach ($endpoints as $endpoint): ?>
                                        <option value="<?php echo htmlspecialchars($endpoint['resource']); ?>">
                                            <?php echo htmlspecialchars($endpoint['resource']); ?> (<?php echo $endpoint['technology']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="callerid"><?php echo $this->lang->line('campaigns_callerid'); ?></label>
                            <input type="text" class="form-control" id="callerid" name="callerid"
                                   value="<?php echo isset($campaign) ? htmlspecialchars($campaign->callerid) : ''; ?>"
                                   placeholder="e.g., 1234567890">
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5><?php echo $this->lang->line('campaigns_section_agent'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="agent_dest_type"><?php echo $this->lang->line('campaigns_agent_dest_type'); ?> *</label>
                            <select class="form-control" id="agent_dest_type" name="agent_dest_type" required>
                                <option value="custom" <?php echo isset($campaign) && $campaign->agent_dest_type == 'custom' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_agent_dest_custom'); ?></option>
                                <option value="exten" <?php echo isset($campaign) && $campaign->agent_dest_type == 'exten' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_agent_dest_extension'); ?></option>
                                <option value="ivr" <?php echo isset($campaign) && $campaign->agent_dest_type == 'ivr' ? 'selected' : ''; ?>><?php echo $this->lang->line('campaigns_agent_dest_ivr'); ?></option>
                            </select>
                        </div>

                        <div class="form-group" id="agent_dest_value_group">
                            <label for="agent_dest_value"><?php echo $this->lang->line('campaigns_agent_dest_value'); ?> *</label>
                            <input type="text" class="form-control" id="agent_dest_value" name="agent_dest_value"
                                   value="<?php echo isset($campaign) ? htmlspecialchars($campaign->agent_dest_value) : ''; ?>"
                                   placeholder="e.g., PJSIP/100 or Local/100@from-internal" required>
                            <small class="form-text text-muted">
                                <span id="agent_help_custom"><?php echo $this->lang->line('campaigns_help_custom'); ?></span>
                                <span id="agent_help_exten" style="display:none;"><?php echo $this->lang->line('campaigns_help_extension'); ?></span>
                                <span id="agent_help_ivr" style="display:none;"><?php echo $this->lang->line('campaigns_help_ivr'); ?></span>
                            </small>
                        </div>

                        <div class="form-group" id="agent_exten_select_group" style="display:none;">
                            <label for="agent_exten_select">Select Extension *</label>
                            <select class="form-control" id="agent_exten_select" name="agent_exten_select">
                                <option value="">-- Select Extension --</option>
                                <?php if (!empty($endpoints)): ?>
                                    <?php foreach ($endpoints as $endpoint): ?>
                                        <option value="<?php echo htmlspecialchars($endpoint['technology']); ?>/<?php echo htmlspecialchars($endpoint['resource']); ?>" <?php echo isset($campaign) && $campaign->agent_dest_type == 'exten' && $campaign->agent_dest_value == $endpoint['technology'].'/'.$endpoint['resource'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($endpoint['resource']); ?> (<?php echo $endpoint['technology']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">SIP/PJSIP extensions from Asterisk</small>
                        </div>

                        <div class="form-group" id="agent_ivr_select_group" style="display:none;">
                            <label for="agent_ivr_select"><?php echo $this->lang->line('campaigns_select_ivr'); ?> *</label>
                            <select class="form-control" id="agent_ivr_select" name="agent_ivr_select">
                                <option value="">-- <?php echo $this->lang->line('campaigns_select_ivr'); ?> --</option>
                                <?php if (!empty($ivr_menus)): ?>
                                    <?php foreach ($ivr_menus as $ivr): ?>
                                        <option value="<?php echo $ivr->id; ?>" <?php echo isset($campaign) && $campaign->agent_dest_type == 'ivr' && $campaign->agent_dest_value == $ivr->id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ivr->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">
                                <a href="<?php echo site_url('ivr'); ?>" target="_blank"><?php echo $this->lang->line('campaigns_manage_ivr'); ?></a>
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="record_calls" name="record_calls" value="1"
                                       <?php echo !isset($campaign) || $campaign->record_calls ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="record_calls">
                                    <?php echo $this->lang->line('campaigns_record_calls'); ?> (<?php echo $this->lang->line('campaigns_help_record_calls'); ?>)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($campaign) ? $this->lang->line('campaigns_update') : $this->lang->line('campaigns_create'); ?>
                </button>
                <a href="<?php echo site_url('campaigns'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?php echo $this->lang->line('btn_cancel'); ?>
                </a>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // On page load, set trunk_select value from trunk_value if editing
    <?php if (isset($campaign) && ($campaign->trunk_type == 'pjsip' || $campaign->trunk_type == 'sip')): ?>
    var savedTrunkValue = '<?php echo htmlspecialchars($campaign->trunk_value); ?>';
    if (savedTrunkValue) {
        $('#trunk_select').val(savedTrunkValue);
    }
    <?php endif; ?>

    // Handle trunk type change
    $('#trunk_type').change(function() {
        var type = $(this).val();

        if (type === 'custom') {
            $('#trunk_value_group').show();
            $('#trunk_select_group').hide();
            $('#trunk_value').prop('required', true);
            $('#trunk_select').prop('required', false);
        } else {
            $('#trunk_value_group').hide();
            $('#trunk_select_group').show();
            $('#trunk_value').prop('required', false);
            $('#trunk_select').prop('required', true);

            // When switching to pjsip/sip, pre-populate dropdown if trunk_value exists
            var currentTrunkValue = $('#trunk_value').val();
            if (currentTrunkValue) {
                $('#trunk_select').val(currentTrunkValue);
            }
        }
    }).trigger('change');

    // Handle agent destination type change
    $('#agent_dest_type').change(function() {
        var type = $(this).val();

        $('.form-text span').hide();
        $('#agent_help_' + type).show();

        if (type === 'ivr') {
            $('#agent_dest_value_group').hide();
            $('#agent_exten_select_group').hide();
            $('#agent_ivr_select_group').show();
            $('#agent_dest_value').prop('required', false);
            $('#agent_exten_select').prop('required', false);
            $('#agent_ivr_select').prop('required', true);
        } else if (type === 'exten') {
            $('#agent_dest_value_group').hide();
            $('#agent_ivr_select_group').hide();
            $('#agent_exten_select_group').show();
            $('#agent_dest_value').prop('required', false);
            $('#agent_ivr_select').prop('required', false);
            $('#agent_exten_select').prop('required', true);

            // Pre-populate dropdown if agent_dest_value exists
            var currentAgentValue = $('#agent_dest_value').val();
            if (currentAgentValue) {
                $('#agent_exten_select').val(currentAgentValue);
            }
        } else {
            $('#agent_dest_value_group').show();
            $('#agent_exten_select_group').hide();
            $('#agent_ivr_select_group').hide();
            $('#agent_dest_value').prop('required', true);
            $('#agent_exten_select').prop('required', false);
            $('#agent_ivr_select').prop('required', false);
            $('#agent_dest_value').prop('placeholder', 'e.g., Local/100@from-internal or PJSIP/100');
        }
    }).trigger('change');

    // Form submission
    $('#campaignForm').submit(function() {
        // If trunk type is not custom, copy selected trunk to trunk_value
        if ($('#trunk_type').val() !== 'custom') {
            $('#trunk_value').val($('#trunk_select').val());
        }

        // If agent dest type is exten, copy selected extension to agent_dest_value
        if ($('#agent_dest_type').val() === 'exten') {
            $('#agent_dest_value').val($('#agent_exten_select').val());
        }

        // If agent dest type is IVR, copy selected IVR ID to agent_dest_value
        if ($('#agent_dest_type').val() === 'ivr') {
            $('#agent_dest_value').val($('#agent_ivr_select').val());
        }
    });
});
</script>
