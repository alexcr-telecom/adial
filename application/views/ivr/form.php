<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo isset($ivr_menu) ? $this->lang->line('ivr_edit') : $this->lang->line('ivr_new'); ?></h2>
            <hr>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="ivrForm">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $this->lang->line('ivr_section_settings'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="campaign_id"><?php echo $this->lang->line('ivr_campaign'); ?> *</label>
                            <select class="form-control" id="campaign_id" name="campaign_id" required <?php echo isset($ivr_menu) ? 'disabled' : ''; ?>>
                                <option value=""><?php echo $this->lang->line('ivr_select_campaign'); ?></option>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <option value="<?php echo $campaign->id; ?>"
                                        <?php echo (isset($ivr_menu) && $ivr_menu->campaign_id == $campaign->id) || ($campaign_id == $campaign->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($campaign->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="name"><?php echo $this->lang->line('ivr_name'); ?> *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo isset($ivr_menu) ? htmlspecialchars($ivr_menu->name) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="audio_file"><?php echo $this->lang->line('ivr_audio_file'); ?> (WAV or MP3) <?php echo !isset($ivr_menu) ? '*' : ''; ?></label>
                            <input type="file" class="form-control-file" id="audio_file" name="audio_file"
                                   accept=".wav,.mp3" <?php echo !isset($ivr_menu) ? 'required' : ''; ?>>
                            <small class="form-text text-muted">
                                <?php echo $this->lang->line('ivr_help_audio_convert'); ?>
                                <?php if (isset($ivr_menu)): ?>
                                    <br><?php echo $this->lang->line('ivr_help_current_file'); ?>: <?php echo htmlspecialchars($ivr_menu->audio_file); ?>
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="timeout"><?php echo $this->lang->line('ivr_timeout'); ?> *</label>
                            <input type="number" class="form-control" id="timeout" name="timeout"
                                   value="<?php echo isset($ivr_menu) ? $ivr_menu->timeout : 3; ?>" min="1" max="60" required>
                            <small class="form-text text-muted"><?php echo $this->lang->line('ivr_help_timeout_default'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="max_digits"><?php echo $this->lang->line('ivr_max_digits'); ?> *</label>
                            <input type="number" class="form-control" id="max_digits" name="max_digits"
                                   value="<?php echo isset($ivr_menu) ? $ivr_menu->max_digits : 1; ?>" min="1" max="10" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $this->lang->line('ivr_section_dtmf_actions'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="actionsContainer">
                            <?php if (isset($ivr_menu) && !empty($ivr_menu->actions)): ?>
                                <?php foreach ($ivr_menu->actions as $index => $action): ?>
                                    <div class="action-row mb-3 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label><?php echo $this->lang->line('ivr_dtmf_digit'); ?></label>
                                                <select class="form-control" name="dtmf_digit[]" required>
                                                    <option value=""><?php echo $this->lang->line('ivr_dtmf_select'); ?></option>
                                                    <?php for($d = 0; $d <= 9; $d++): ?>
                                                        <option value="<?php echo $d; ?>" <?php echo $action->dtmf_digit == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                                                    <?php endfor; ?>
                                                    <option value="*" <?php echo $action->dtmf_digit == '*' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_dtmf_star'); ?></option>
                                                    <option value="#" <?php echo $action->dtmf_digit == '#' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_dtmf_hash'); ?></option>
                                                    <option value="i" <?php echo $action->dtmf_digit == 'i' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_dtmf_invalid'); ?></option>
                                                    <option value="t" <?php echo $action->dtmf_digit == 't' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_dtmf_timeout'); ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label><?php echo $this->lang->line('ivr_action_type'); ?></label>
                                                <select class="form-control action-type" name="action_type[]" required>
                                                    <option value="exten" <?php echo $action->action_type == 'exten' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_action_call_extension'); ?></option>
                                                    <option value="queue" <?php echo $action->action_type == 'queue' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_action_queue'); ?></option>
                                                    <option value="hangup" <?php echo $action->action_type == 'hangup' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_action_hangup'); ?></option>
                                                    <option value="playback" <?php echo $action->action_type == 'playback' ? 'selected' : ''; ?>><?php echo $this->lang->line('ivr_action_playback'); ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-5 action-value-container">
                                                <label><?php echo $this->lang->line('ivr_action_value'); ?></label>
                                                <input type="text" class="form-control action-value-field" name="action_value[]"
                                                       value="<?php echo htmlspecialchars($action->action_value); ?>"
                                                       placeholder="<?php echo $this->lang->line('ivr_help_action_placeholder'); ?>" <?php echo $action->action_type !== 'hangup' ? 'required' : ''; ?>>
                                            </div>
                                            <div class="col-md-1">
                                                <label>&nbsp;</label><br>
                                                <button type="button" class="btn btn-danger btn-sm btn-remove-action">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default action row 1: Invalid input (i) -> Hangup -->
                                <div class="action-row mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label><?php echo $this->lang->line('ivr_dtmf_digit'); ?></label>
                                            <select class="form-control" name="dtmf_digit[]" required>
                                                <option value=""><?php echo $this->lang->line('ivr_dtmf_select'); ?></option>
                                                <?php for($d = 0; $d <= 9; $d++): ?>
                                                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                                <?php endfor; ?>
                                                <option value="*"><?php echo $this->lang->line('ivr_dtmf_star'); ?></option>
                                                <option value="#"><?php echo $this->lang->line('ivr_dtmf_hash'); ?></option>
                                                <option value="i" selected><?php echo $this->lang->line('ivr_dtmf_invalid'); ?></option>
                                                <option value="t"><?php echo $this->lang->line('ivr_dtmf_timeout'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label><?php echo $this->lang->line('ivr_action_type'); ?></label>
                                            <select class="form-control action-type" name="action_type[]" required>
                                                <option value="exten"><?php echo $this->lang->line('ivr_action_call_extension'); ?></option>
                                                <option value="queue"><?php echo $this->lang->line('ivr_action_queue'); ?></option>
                                                <option value="hangup" selected><?php echo $this->lang->line('ivr_action_hangup'); ?></option>
                                                <option value="playback"><?php echo $this->lang->line('ivr_action_playback'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 action-value-container">
                                            <label><?php echo $this->lang->line('ivr_action_value'); ?></label>
                                            <input type="text" class="form-control action-value-field" name="action_value[]"
                                                   placeholder="<?php echo $this->lang->line('ivr_help_action_placeholder'); ?>">
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-action">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default action row 2: Timeout (t) -> Hangup -->
                                <div class="action-row mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label><?php echo $this->lang->line('ivr_dtmf_digit'); ?></label>
                                            <select class="form-control" name="dtmf_digit[]" required>
                                                <option value=""><?php echo $this->lang->line('ivr_dtmf_select'); ?></option>
                                                <?php for($d = 0; $d <= 9; $d++): ?>
                                                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                                <?php endfor; ?>
                                                <option value="*"><?php echo $this->lang->line('ivr_dtmf_star'); ?></option>
                                                <option value="#"><?php echo $this->lang->line('ivr_dtmf_hash'); ?></option>
                                                <option value="i"><?php echo $this->lang->line('ivr_dtmf_invalid'); ?></option>
                                                <option value="t" selected><?php echo $this->lang->line('ivr_dtmf_timeout'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label><?php echo $this->lang->line('ivr_action_type'); ?></label>
                                            <select class="form-control action-type" name="action_type[]" required>
                                                <option value="exten"><?php echo $this->lang->line('ivr_action_call_extension'); ?></option>
                                                <option value="queue"><?php echo $this->lang->line('ivr_action_queue'); ?></option>
                                                <option value="hangup" selected><?php echo $this->lang->line('ivr_action_hangup'); ?></option>
                                                <option value="playback"><?php echo $this->lang->line('ivr_action_playback'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 action-value-container">
                                            <label><?php echo $this->lang->line('ivr_action_value'); ?></label>
                                            <input type="text" class="form-control action-value-field" name="action_value[]"
                                                   placeholder="<?php echo $this->lang->line('ivr_help_action_placeholder'); ?>">
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-action">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn btn-secondary" id="btnAddAction">
                            <i class="fas fa-plus"></i> <?php echo $this->lang->line('ivr_add_action'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo isset($ivr_menu) ? $this->lang->line('ivr_update') : $this->lang->line('ivr_create'); ?>
                </button>
                <a href="<?php echo site_url('ivr'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?php echo $this->lang->line('btn_cancel'); ?>
                </a>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Add action row
    $('#btnAddAction').click(function() {
        var newRow = `
            <div class="action-row mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-3">
                        <label><?php echo $this->lang->line('ivr_dtmf_digit'); ?></label>
                        <select class="form-control" name="dtmf_digit[]" required>
                            <option value=""><?php echo $this->lang->line('ivr_dtmf_select'); ?></option>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="*"><?php echo $this->lang->line('ivr_dtmf_star'); ?></option>
                            <option value="#"><?php echo $this->lang->line('ivr_dtmf_hash'); ?></option>
                            <option value="i"><?php echo $this->lang->line('ivr_dtmf_invalid'); ?></option>
                            <option value="t"><?php echo $this->lang->line('ivr_dtmf_timeout'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><?php echo $this->lang->line('ivr_action_type'); ?></label>
                        <select class="form-control action-type" name="action_type[]" required>
                            <option value="exten"><?php echo $this->lang->line('ivr_action_call_extension'); ?></option>
                            <option value="queue"><?php echo $this->lang->line('ivr_action_queue'); ?></option>
                            <option value="hangup"><?php echo $this->lang->line('ivr_action_hangup'); ?></option>
                            <option value="playback"><?php echo $this->lang->line('ivr_action_playback'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-5 action-value-container">
                        <label><?php echo $this->lang->line('ivr_action_value'); ?></label>
                        <input type="text" class="form-control action-value-field" name="action_value[]"
                               placeholder="<?php echo $this->lang->line('ivr_help_action_placeholder'); ?>" required>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-action">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#actionsContainer').append(newRow);
    });

    // Remove action row
    $(document).on('click', '.btn-remove-action', function() {
        if ($('.action-row').length > 1) {
            $(this).closest('.action-row').remove();
        } else {
            alert('<?php echo $this->lang->line('ivr_at_least_one_action'); ?>');
        }
    });

    // Handle action type change to show/hide action value field
    $(document).on('change', '.action-type', function() {
        var actionType = $(this).val();
        var $actionRow = $(this).closest('.action-row');
        var $actionValueContainer = $actionRow.find('.action-value-container');
        var $actionValueField = $actionRow.find('.action-value-field');

        if (actionType === 'hangup') {
            $actionValueContainer.hide();
            $actionValueField.prop('required', false).val('');
        } else {
            $actionValueContainer.show();
            $actionValueField.prop('required', true);
        }
    });

    // Trigger on page load for existing rows
    $('.action-type').each(function() {
        $(this).trigger('change');
    });
});
</script>
