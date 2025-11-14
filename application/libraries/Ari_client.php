<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Asterisk ARI Client Library
 *
 * Handles all communication with Asterisk REST Interface
 */
class Ari_client {

    private $CI;
    private $base_url;
    private $username;
    private $password;
    private $debug;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Ari_log_model');

        // Load ARI configuration
        $this->base_url = $this->CI->config->item('ari_base_url');
        $this->username = $this->CI->config->item('ari_username');
        $this->password = $this->CI->config->item('ari_password');
        $this->debug = $this->CI->config->item('ari_debug');
    }

    /**
     * Make ARI REST API request
     */
    private function request($method, $endpoint, $data = array()) {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Set method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log request/response if debug is enabled
        if ($this->debug) {
            $this->CI->Ari_log_model->log_request(
                $method,
                $endpoint,
                $data,
                $response,
                $status_code,
                $error
            );
        }

        if ($error) {
            return array('success' => false, 'error' => $error);
        }

        $decoded = json_decode($response, true);
        return array(
            'success' => ($status_code >= 200 && $status_code < 300),
            'status_code' => $status_code,
            'data' => $decoded ? $decoded : $response
        );
    }

    /**
     * Get Asterisk info
     */
    public function get_asterisk_info() {
        return $this->request('GET', '/asterisk/info');
    }

    /**
     * Get all endpoints (SIP/PJSIP)
     */
    public function get_endpoints() {
        return $this->request('GET', '/endpoints');
    }

    /**
     * Get all channels
     */
    public function get_channels() {
        return $this->request('GET', '/channels');
    }

    /**
     * Get specific channel
     */
    public function get_channel($channel_id) {
        return $this->request('GET', '/channels/' . $channel_id);
    }

    /**
     * Originate a new channel
     */
    public function originate($endpoint, $extension, $context = 'from-internal', $callerid = null, $variables = array()) {
        $data = array(
            'endpoint' => $endpoint,
            'extension' => $extension,
            'context' => $context,
            'priority' => 1,
            'app' => $this->CI->config->item('ari_stasis_app')
        );

        if ($callerid) {
            $data['callerId'] = $callerid;
        }

        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                $data['variables[' . $key . ']'] = $value;
            }
        }

        return $this->request('POST', '/channels', $data);
    }

    /**
     * Answer a channel
     */
    public function answer_channel($channel_id) {
        return $this->request('POST', '/channels/' . $channel_id . '/answer');
    }

    /**
     * Hangup a channel
     */
    public function hangup_channel($channel_id, $reason = 'normal') {
        return $this->request('DELETE', '/channels/' . $channel_id, array('reason' => $reason));
    }

    /**
     * Create a bridge
     */
    public function create_bridge($type = 'mixing') {
        return $this->request('POST', '/bridges', array('type' => $type));
    }

    /**
     * Add channel to bridge
     */
    public function add_channel_to_bridge($bridge_id, $channel_id) {
        return $this->request('POST', '/bridges/' . $bridge_id . '/addChannel', array('channel' => $channel_id));
    }

    /**
     * Remove channel from bridge
     */
    public function remove_channel_from_bridge($bridge_id, $channel_id) {
        return $this->request('POST', '/bridges/' . $bridge_id . '/removeChannel', array('channel' => $channel_id));
    }

    /**
     * Destroy bridge
     */
    public function destroy_bridge($bridge_id) {
        return $this->request('DELETE', '/bridges/' . $bridge_id);
    }

    /**
     * Play audio on channel
     */
    public function play_on_channel($channel_id, $media) {
        return $this->request('POST', '/channels/' . $channel_id . '/play', array('media' => 'sound:' . $media));
    }

    /**
     * Start recording on channel (snoop)
     */
    public function snoop_channel($channel_id, $spy = 'in', $whisper = 'none', $app = null) {
        $app = $app ?: $this->CI->config->item('ari_stasis_app');
        $data = array(
            'spy' => $spy,
            'whisper' => $whisper,
            'app' => $app,
            'appArgs' => 'recording'
        );
        return $this->request('POST', '/channels/' . $channel_id . '/snoop', $data);
    }

    /**
     * Record channel
     */
    public function record_channel($channel_id, $name, $format = 'wav', $max_duration = 0, $max_silence = 0) {
        $data = array(
            'name' => $name,
            'format' => $format,
            'ifExists' => 'overwrite'
        );

        if ($max_duration > 0) {
            $data['maxDurationSeconds'] = $max_duration;
        }

        if ($max_silence > 0) {
            $data['maxSilenceSeconds'] = $max_silence;
        }

        return $this->request('POST', '/channels/' . $channel_id . '/record', $data);
    }

    /**
     * Stop recording
     */
    public function stop_recording($recording_name) {
        return $this->request('POST', '/recordings/live/' . $recording_name . '/stop');
    }

    /**
     * Get stored recording
     */
    public function get_recording($recording_name) {
        return $this->request('GET', '/recordings/stored/' . $recording_name);
    }

    /**
     * Delete stored recording
     */
    public function delete_recording($recording_name) {
        return $this->request('DELETE', '/recordings/stored/' . $recording_name);
    }

    /**
     * Get sounds
     */
    public function get_sounds() {
        return $this->request('GET', '/sounds');
    }

    /**
     * Start DTMF detection on channel
     */
    public function start_dtmf($channel_id) {
        return $this->request('POST', '/channels/' . $channel_id . '/dtmf');
    }
}
