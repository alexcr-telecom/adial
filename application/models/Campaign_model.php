<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all campaigns
     */
    public function get_all($status = null) {
        if ($status) {
            $this->db->where('status', $status);
        }
        return $this->db->order_by('created_at', 'DESC')
                        ->get('campaigns')
                        ->result();
    }

    /**
     * Get campaign by ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)
                        ->get('campaigns')
                        ->row();
    }

    /**
     * Create new campaign
     */
    public function create($data) {
        $insert_data = array(
            'name' => $data['name'],
            'description' => isset($data['description']) ? $data['description'] : '',
            'trunk_type' => $data['trunk_type'],
            'trunk_value' => $data['trunk_value'],
            'callerid' => isset($data['callerid']) ? $data['callerid'] : null,
            'agent_dest_type' => $data['agent_dest_type'],
            'agent_dest_value' => isset($data['agent_dest_value']) ? $data['agent_dest_value'] : null,
            'record_calls' => isset($data['record_calls']) ? $data['record_calls'] : 0,
            'concurrent_calls' => isset($data['concurrent_calls']) ? $data['concurrent_calls'] : 1,
            'retry_times' => isset($data['retry_times']) ? $data['retry_times'] : 0,
            'retry_delay' => isset($data['retry_delay']) ? $data['retry_delay'] : 300
        );

        if ($this->db->insert('campaigns', $insert_data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update campaign
     */
    public function update($id, $data) {
        $update_data = array();

        $allowed_fields = array(
            'name', 'description', 'trunk_type', 'trunk_value', 'callerid',
            'agent_dest_type', 'agent_dest_value', 'record_calls',
            'concurrent_calls', 'retry_times', 'retry_delay'
        );

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }

        if (!empty($update_data)) {
            $update_data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->where('id', $id)
                           ->update('campaigns', $update_data);
        }

        return false;
    }

    /**
     * Update campaign status
     */
    public function update_status($id, $status) {
        return $this->db->where('id', $id)
                        ->update('campaigns', array(
                            'status' => $status,
                            'updated_at' => date('Y-m-d H:i:s')
                        ));
    }

    /**
     * Delete campaign
     */
    public function delete($id) {
        return $this->db->where('id', $id)
                        ->delete('campaigns');
    }

    /**
     * Get campaign statistics
     */
    public function get_stats($campaign_id) {
        $stats = array();

        // Get total numbers
        $stats['total'] = $this->db->where('campaign_id', $campaign_id)
                                   ->count_all_results('campaign_numbers');

        // Get numbers by status
        $status_counts = $this->db->select('status, COUNT(*) as count')
                                  ->where('campaign_id', $campaign_id)
                                  ->group_by('status')
                                  ->get('campaign_numbers')
                                  ->result();

        // Initialize status counters
        $stats['pending'] = 0;
        $stats['calling'] = 0;
        $stats['answered'] = 0;
        $stats['completed'] = 0;
        $stats['failed'] = 0;
        $stats['no_answer'] = 0;
        $stats['busy'] = 0;

        foreach ($status_counts as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        // Get total calls
        $stats['total_calls'] = $this->db->where('campaign_id', $campaign_id)
                                         ->count_all_results('cdr');

        // Get answered calls
        $stats['answered_calls'] = $this->db->where('campaign_id', $campaign_id)
                                            ->where('disposition', 'answered')
                                            ->count_all_results('cdr');

        // Convert array to object for easier access in views
        return (object)$stats;
    }

    /**
     * Get active campaigns
     */
    public function get_active() {
        return $this->db->where('status', 'running')
                        ->get('campaigns')
                        ->result();
    }
}
