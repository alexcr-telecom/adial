<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all settings
     */
    public function get_all() {
        $query = $this->db->get('settings');
        return $query->result();
    }

    /**
     * Get setting by key
     */
    public function get_by_key($key) {
        $this->db->where('setting_key', $key);
        $query = $this->db->get('settings');
        return $query->row();
    }

    /**
     * Get setting value by key
     */
    public function get_value($key, $default = null) {
        $setting = $this->get_by_key($key);
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Update setting
     */
    public function update($key, $value) {
        $data = array(
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->db->where('setting_key', $key);
        return $this->db->update('settings', $data);
    }

    /**
     * Create new setting
     */
    public function create($key, $value, $description = '') {
        $data = array(
            'setting_key' => $key,
            'setting_value' => $value,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        );

        return $this->db->insert('settings', $data);
    }

    /**
     * Delete setting
     */
    public function delete($key) {
        $this->db->where('setting_key', $key);
        return $this->db->delete('settings');
    }
}
