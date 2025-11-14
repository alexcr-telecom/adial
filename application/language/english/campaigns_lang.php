<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['campaigns_title'] = 'Campaigns';
$lang['campaigns_new'] = 'New Campaign';
$lang['campaigns_edit'] = 'Edit Campaign';
$lang['campaigns_view'] = 'View Campaign';

// Form fields
$lang['campaigns_name'] = 'Campaign Name';
$lang['campaigns_description'] = 'Description';
$lang['campaigns_concurrent_calls'] = 'Concurrent Calls';
$lang['campaigns_retry_times'] = 'Retry Times';
$lang['campaigns_retry_delay'] = 'Retry Delay (seconds)';
$lang['campaigns_trunk_type'] = 'Trunk Type';
$lang['campaigns_trunk_value'] = 'Trunk Value';
$lang['campaigns_callerid'] = 'Caller ID';
$lang['campaigns_record_calls'] = 'Record Calls';

// Trunk types
$lang['campaigns_trunk_custom'] = 'Custom';
$lang['campaigns_trunk_pjsip'] = 'PJSIP';
$lang['campaigns_trunk_sip'] = 'SIP';
$lang['campaigns_select_trunk'] = 'Select Trunk';

// Agent destination
$lang['campaigns_agent_dest_type'] = 'Destination Type';
$lang['campaigns_agent_dest_value'] = 'Destination Value';
$lang['campaigns_agent_dest_custom'] = 'Custom';
$lang['campaigns_agent_dest_extension'] = 'Extension';
$lang['campaigns_agent_dest_ivr'] = 'IVR';
$lang['campaigns_select_ivr'] = 'Select IVR Menu';

// Help text
$lang['campaigns_help_max_calls'] = 'Maximum number of simultaneous calls';
$lang['campaigns_help_trunk_value'] = 'Use ${EXTEN} for number substitution';
$lang['campaigns_help_record_calls'] = 'Both channels will be recorded and mixed into stereo MP3';
$lang['campaigns_help_custom'] = 'Enter full dial string (e.g., PJSIP/100, Local/100@from-internal)';
$lang['campaigns_help_extension'] = 'Enter extension number (e.g., 100)';
$lang['campaigns_help_ivr'] = 'Select IVR menu from dropdown below';

// Sections
$lang['campaigns_section_basic'] = 'Basic Information';
$lang['campaigns_section_trunk'] = 'Trunk Configuration';
$lang['campaigns_section_agent'] = 'Agent Destination';

// Buttons
$lang['campaigns_create'] = 'Create Campaign';
$lang['campaigns_update'] = 'Update Campaign';
$lang['campaigns_start'] = 'Start';
$lang['campaigns_pause'] = 'Pause';
$lang['campaigns_stop'] = 'Stop';
$lang['campaigns_upload_numbers'] = 'Upload Numbers';
$lang['campaigns_manage_ivr'] = 'Manage IVR Menus';

// Table columns
$lang['campaigns_id'] = 'ID';
$lang['campaigns_total_numbers'] = 'Total Numbers';
$lang['campaigns_pending'] = 'Pending';
$lang['campaigns_completed'] = 'Completed';

// Messages
$lang['campaigns_no_campaigns'] = 'No campaigns found. Create your first campaign!';
$lang['campaigns_confirm_delete'] = 'Are you sure you want to delete this campaign? This action cannot be undone.';
$lang['campaigns_confirm_start'] = 'Are you sure you want to start this campaign?';
$lang['campaigns_confirm_stop'] = 'Are you sure you want to stop this campaign?';
$lang['campaigns_error'] = 'Error';
$lang['campaigns_failed_control'] = 'Failed to control campaign';
