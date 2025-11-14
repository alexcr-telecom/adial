<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ari_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Log ARI request/response
     */
    public function log_request($method, $endpoint, $request_data, $response_data, $status_code, $error = null) {
        $data = array(
            'log_type' => $error ? 'error' : 'request',
            'method' => $method,
            'endpoint' => $endpoint,
            'request_data' => json_encode($request_data),
            'response_data' => is_string($response_data) ? $response_data : json_encode($response_data),
            'status_code' => $status_code,
            'error_message' => $error
        );

        return $this->db->insert('ari_logs', $data);
    }

    /**
     * Log ARI event
     */
    public function log_event($event_data) {
        $data = array(
            'log_type' => 'event',
            'response_data' => json_encode($event_data)
        );

        return $this->db->insert('ari_logs', $data);
    }

    /**
     * Get logs with pagination
     */
    public function get_logs($limit = 100, $offset = 0, $log_type = null) {
        if ($log_type) {
            $this->db->where('log_type', $log_type);
        }

        return $this->db->order_by('created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get('ari_logs')
                        ->result();
    }

    /**
     * Clear old logs (older than X days)
     */
    public function clear_old_logs($days = 30) {
        $date = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        return $this->db->where('created_at <', $date)
                        ->delete('ari_logs');
    }
}
