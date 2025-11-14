<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign_number_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Add number to campaign
     */
    public function add_number($campaign_id, $phone_number, $data = null) {
        $insert_data = array(
            'campaign_id' => $campaign_id,
            'phone_number' => $phone_number,
            'status' => 'pending',
            'attempts' => 0,
            'data' => $data ? json_encode($data) : null
        );

        return $this->db->insert('campaign_numbers', $insert_data);
    }

    /**
     * Bulk add numbers from CSV
     */
    public function bulk_add($campaign_id, $numbers) {
        $insert_batch = array();

        foreach ($numbers as $number) {
            $insert_batch[] = array(
                'campaign_id' => $campaign_id,
                'phone_number' => is_array($number) ? $number['phone'] : $number,
                'status' => 'pending',
                'attempts' => 0,
                'data' => is_array($number) && isset($number['data']) ? json_encode($number['data']) : null
            );
        }

        if (!empty($insert_batch)) {
            return $this->db->insert_batch('campaign_numbers', $insert_batch);
        }

        return false;
    }

    /**
     * Get numbers for campaign
     */
    public function get_by_campaign($campaign_id, $status = null, $limit = null, $offset = 0) {
        $this->db->where('campaign_id', $campaign_id);

        if ($status) {
            $this->db->where('status', $status);
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->order_by('created_at', 'ASC')
                        ->get('campaign_numbers')
                        ->result();
    }

    /**
     * Get next number to call for campaign
     */
    public function get_next_to_call($campaign_id) {
        return $this->db->where('campaign_id', $campaign_id)
                        ->where('status', 'pending')
                        ->order_by('created_at', 'ASC')
                        ->limit(1)
                        ->get('campaign_numbers')
                        ->row();
    }

    /**
     * Update number status
     */
    public function update_status($id, $status, $increment_attempts = false) {
        $update_data = array(
            'status' => $status,
            'last_attempt' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($increment_attempts) {
            $this->db->set('attempts', 'attempts + 1', FALSE);
        }

        return $this->db->where('id', $id)
                        ->update('campaign_numbers', $update_data);
    }

    /**
     * Reset number status for retry
     */
    public function reset_for_retry($campaign_id, $max_attempts) {
        return $this->db->where('campaign_id', $campaign_id)
                        ->where('attempts <', $max_attempts)
                        ->where_in('status', array('failed', 'no_answer', 'busy'))
                        ->update('campaign_numbers', array(
                            'status' => 'pending',
                            'updated_at' => date('Y-m-d H:i:s')
                        ));
    }

    /**
     * Delete number
     */
    public function delete($id) {
        return $this->db->where('id', $id)
                        ->delete('campaign_numbers');
    }

    /**
     * Delete all numbers for campaign
     */
    public function delete_by_campaign($campaign_id) {
        return $this->db->where('campaign_id', $campaign_id)
                        ->delete('campaign_numbers');
    }

    /**
     * Get number by ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)
                        ->get('campaign_numbers')
                        ->row();
    }
}
