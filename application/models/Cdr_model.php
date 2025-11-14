<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cdr_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Create CDR record
     */
    public function create($data) {
        return $this->db->insert('cdr', $data);
    }

    /**
     * Get CDR by ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)
                        ->get('cdr')
                        ->row();
    }

    /**
     * Update CDR record
     */
    public function update($id, $data) {
        return $this->db->where('id', $id)
                        ->update('cdr', $data);
    }

    /**
     * Get CDR records with filters
     */
    public function get_all($filters = array(), $limit = 100, $offset = 0) {
        if (isset($filters['campaign_id'])) {
            $this->db->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['disposition'])) {
            $this->db->where('disposition', $filters['disposition']);
        }

        if (isset($filters['start_date'])) {
            $this->db->where('start_time >=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $this->db->where('start_time <=', $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $this->db->group_start();
            $this->db->like('callerid', $filters['search']);
            $this->db->or_like('destination', $filters['search']);
            $this->db->or_like('agent', $filters['search']);
            $this->db->group_end();
        }

        return $this->db->select('cdr.*, campaigns.name as campaign_name')
                        ->join('campaigns', 'campaigns.id = cdr.campaign_id', 'left')
                        ->order_by('cdr.start_time', 'DESC')
                        ->limit($limit, $offset)
                        ->get('cdr')
                        ->result();
    }

    /**
     * Count CDR records with filters
     */
    public function count_all($filters = array()) {
        if (isset($filters['campaign_id'])) {
            $this->db->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['disposition'])) {
            $this->db->where('disposition', $filters['disposition']);
        }

        if (isset($filters['start_date'])) {
            $this->db->where('start_time >=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $this->db->where('start_time <=', $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $this->db->group_start();
            $this->db->like('callerid', $filters['search']);
            $this->db->or_like('destination', $filters['search']);
            $this->db->or_like('agent', $filters['search']);
            $this->db->group_end();
        }

        return $this->db->count_all_results('cdr');
    }

    /**
     * Get CDR by uniqueid
     */
    public function get_by_uniqueid($uniqueid) {
        return $this->db->where('uniqueid', $uniqueid)
                        ->get('cdr')
                        ->row();
    }

    /**
     * Delete old CDR records
     */
    public function delete_old($days = 90) {
        $date = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        return $this->db->where('start_time <', $date)
                        ->delete('cdr');
    }
}
