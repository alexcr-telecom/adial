<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ivr_menu_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all IVR menus
     */
    public function get_all() {
        $query = $this->db->order_by('created_at', 'DESC')
                          ->get('ivr_menus');
        return $query->result();
    }

    /**
     * Get IVR menu by ID
     */
    public function get_by_id($id) {
        $query = $this->db->where('id', $id)
                          ->get('ivr_menus');
        return $query->row();
    }

    /**
     * Get IVR menus by campaign ID
     */
    public function get_by_campaign($campaign_id) {
        $query = $this->db->where('campaign_id', $campaign_id)
                          ->order_by('created_at', 'DESC')
                          ->get('ivr_menus');
        return $query->result();
    }

    /**
     * Create IVR menu
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('ivr_menus', $data);
        return $this->db->insert_id();
    }

    /**
     * Update IVR menu
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('ivr_menus', $data);
    }

    /**
     * Delete IVR menu
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('ivr_menus');
    }

    /**
     * Get IVR menu with actions
     */
    public function get_with_actions($id) {
        $menu = $this->get_by_id($id);

        if ($menu) {
            $this->load->model('Ivr_action_model');
            $menu->actions = $this->Ivr_action_model->get_by_menu($id);
        }

        return $menu;
    }
}
