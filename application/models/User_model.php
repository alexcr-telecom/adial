<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all users
     */
    public function get_all() {
        return $this->db->order_by('created_at', 'DESC')
                        ->get('users')
                        ->result();
    }

    /**
     * Get user by ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)
                        ->get('users')
                        ->row();
    }

    /**
     * Get user by username
     */
    public function get_by_username($username) {
        return $this->db->where('username', $username)
                        ->get('users')
                        ->row();
    }

    /**
     * Create new user
     */
    public function create($data) {
        $insert_data = array(
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'email' => isset($data['email']) ? $data['email'] : null,
            'full_name' => isset($data['full_name']) ? $data['full_name'] : null,
            'role' => isset($data['role']) ? $data['role'] : 'user',
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1,
            'created_at' => date('Y-m-d H:i:s')
        );

        if ($this->db->insert('users', $insert_data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $update_data = array();

        $allowed_fields = array('username', 'email', 'full_name', 'role', 'is_active');

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }

        // Handle password separately
        if (!empty($data['password'])) {
            $update_data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!empty($update_data)) {
            $update_data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->where('id', $id)
                           ->update('users', $update_data);
        }

        return false;
    }

    /**
     * Delete user
     */
    public function delete($id) {
        // Don't allow deleting the last admin
        $admin_count = $this->db->where('role', 'admin')
                                ->where('is_active', 1)
                                ->count_all_results('users');

        $user = $this->get_by_id($id);
        if ($user && $user->role == 'admin' && $admin_count <= 1) {
            return false; // Cannot delete last admin
        }

        return $this->db->where('id', $id)
                        ->delete('users');
    }

    /**
     * Update last login
     */
    public function update_last_login($id) {
        return $this->db->where('id', $id)
                        ->update('users', array('last_login' => date('Y-m-d H:i:s')));
    }

    /**
     * Verify login credentials
     */
    public function verify_login($username, $password) {
        $user = $this->get_by_username($username);

        if ($user && $user->is_active && password_verify($password, $user->password)) {
            return $user;
        }

        return false;
    }
}
