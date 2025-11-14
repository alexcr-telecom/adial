<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Asterisk ARI Configuration
|--------------------------------------------------------------------------
|
| Configuration for Asterisk REST Interface (ARI) connections
|
*/

$config['ari_host'] = 'localhost';
$config['ari_port'] = '8088';
$config['ari_username'] = 'dialer';
$config['ari_password'] = '76e6d233237c5323b9bb71860e322b61';
$config['ari_stasis_app'] = 'dialer';
$config['ari_ws_url'] = 'ws://localhost:8088/ari/events';
$config['ari_base_url'] = 'http://localhost:8088/ari';

// Debug mode for logging all ARI requests/responses
$config['ari_debug'] = TRUE;

// Asterisk sounds directory for IVR files
$config['asterisk_sounds_dir'] = '/var/lib/asterisk/sounds/dialer/';

// Recording settings
$config['recording_enabled'] = TRUE;
$config['recording_format'] = 'wav';
$config['recording_mix_format'] = 'mp3';
