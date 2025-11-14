<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ivr_action_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all IVR actions
     */
    public function get_all() {
        $query = $this->db->order_by('created_at', 'DESC')
                          ->get('ivr_actions');
        return $query->result();
    }

    /**
     * Get IVR action by ID
     */
    public function get_by_id($id) {
        $query = $this->db->where('id', $id)
                          ->get('ivr_actions');
        return $query->row();
    }

    /**
     * Get IVR actions by menu ID
     */
    public function get_by_menu($ivr_menu_id) {
        $query = $this->db->where('ivr_menu_id', $ivr_menu_id)
                          ->order_by('dtmf_digit', 'ASC')
                          ->get('ivr_actions');
        return $query->result();
    }

    /**
     * Create IVR action
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('ivr_actions', $data);
        return $this->db->insert_id();
    }

    /**
     * Update IVR action
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('ivr_actions', $data);
    }

    /**
     * Delete IVR action
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('ivr_actions');
    }

    /**
     * Delete all actions for a menu
     */
    public function delete_by_menu($ivr_menu_id) {
        $this->db->where('ivr_menu_id', $ivr_menu_id);
        return $this->db->delete('ivr_actions');
    }

    /**
     * Bulk create actions
     */
    public function bulk_create($ivr_menu_id, $actions) {
        if (empty($actions)) {
            return false;
        }

        foreach ($actions as $action) {
            $action['ivr_menu_id'] = $ivr_menu_id;
            $this->create($action);
        }

        return true;
    }
}
