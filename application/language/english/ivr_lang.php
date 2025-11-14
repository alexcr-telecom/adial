<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['ivr_title'] = 'IVR Menus';
$lang['ivr_new'] = 'New IVR Menu';
$lang['ivr_edit'] = 'Edit IVR Menu';
$lang['ivr_view'] = 'View IVR Menu';

// Form fields
$lang['ivr_name'] = 'Menu Name';
$lang['ivr_campaign'] = 'Campaign';
$lang['ivr_select_campaign'] = '-- Select Campaign --';
$lang['ivr_audio_file'] = 'Audio File';
$lang['ivr_current_audio'] = 'Current Audio';
$lang['ivr_timeout'] = 'Timeout (seconds)';
$lang['ivr_max_digits'] = 'Max Digits';

// Actions section
$lang['ivr_actions'] = 'IVR Actions';
$lang['ivr_dtmf_digit'] = 'DTMF Digit';
$lang['ivr_action_type'] = 'Action Type';
$lang['ivr_action_value'] = 'Action Value';
$lang['ivr_add_action'] = 'Add Action';

// Action types
$lang['ivr_action_hangup'] = 'Hangup';
$lang['ivr_action_extension'] = 'Extension';
$lang['ivr_action_call_extension'] = 'Call Extension';
$lang['ivr_action_ivr'] = 'IVR Menu';
$lang['ivr_action_voicemail'] = 'Voicemail';
$lang['ivr_action_queue'] = 'Queue';
$lang['ivr_action_playback'] = 'Playback';

// Help text
$lang['ivr_help_audio'] = 'Upload WAV or MP3 file (max 10MB)';
$lang['ivr_help_audio_convert'] = 'File will be automatically converted to Asterisk format (8000Hz, Mono WAV).';
$lang['ivr_help_current_file'] = 'Current file';
$lang['ivr_help_timeout'] = 'Time to wait for user input';
$lang['ivr_help_timeout_default'] = 'How long to wait for DTMF input (default: 3 seconds)';
$lang['ivr_help_max_digits'] = 'Maximum number of digits to collect';
$lang['ivr_help_dtmf'] = 'Press digit (0-9, *, #) or "timeout" for no input';
$lang['ivr_help_action_placeholder'] = 'e.g., PJSIP/100';

// Buttons
$lang['ivr_create'] = 'Create IVR Menu';
$lang['ivr_update'] = 'Update IVR Menu';
$lang['ivr_manage_menus'] = 'Manage IVR Menus';

// Sections
$lang['ivr_section_settings'] = 'IVR Menu Settings';
$lang['ivr_section_dtmf_actions'] = 'DTMF Actions';

// Table columns
$lang['ivr_id'] = 'ID';
$lang['ivr_operations'] = 'Operations';

// DTMF options
$lang['ivr_dtmf_select'] = '-- Select --';
$lang['ivr_dtmf_star'] = '* (Star)';
$lang['ivr_dtmf_hash'] = '# (Hash)';
$lang['ivr_dtmf_invalid'] = 'i (Invalid Input)';
$lang['ivr_dtmf_timeout'] = 't (Timeout)';

// View page
$lang['ivr_menu_details'] = 'Menu Details';
$lang['ivr_play_audio'] = 'Play Audio';
$lang['ivr_pause'] = 'Pause';
$lang['ivr_audio_not_supported'] = 'Your browser does not support the audio element.';
$lang['ivr_seconds'] = 'seconds';
$lang['ivr_no_actions'] = 'No DTMF actions configured';
$lang['ivr_action_goto_ivr'] = 'Go to IVR';

// Messages
$lang['ivr_no_menus'] = 'No IVR menus found. Create your first IVR menu!';
$lang['ivr_confirm_delete'] = 'Are you sure you want to delete this IVR menu? This action cannot be undone.';
$lang['ivr_audio_required'] = 'Audio file is required';
$lang['ivr_at_least_one_action'] = 'At least one action is required';
