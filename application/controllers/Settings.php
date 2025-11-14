<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Settings_model');
        $this->load->model('User_model');
    }

    /**
     * Settings page
     */
    public function index() {
        if ($this->input->post()) {
            // Update all settings
            $settings = $this->Settings_model->get_all();

            foreach ($settings as $setting) {
                $post_value = $this->input->post($setting->setting_key);
                if ($post_value !== null) {
                    $this->Settings_model->update($setting->setting_key, $post_value);
                }
            }

            $this->session->set_flashdata('success', 'Settings updated successfully');
            redirect('settings');
        }

        $data['settings'] = $this->Settings_model->get_all();
        $data['users'] = $this->User_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('settings/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Test ARI connection
     */
    public function test_ari() {
        $this->load->library('ari_client');

        $result = $this->ari_client->test_connection();

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Add user (AJAX)
     */
    public function add_user() {
        header('Content-Type: application/json');

        // Admin only
        if (!$this->auth->is_admin()) {
            echo json_encode(array('success' => false, 'message' => 'Permission denied'));
            return;
        }

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        $full_name = $this->input->post('full_name');
        $role = $this->input->post('role');

        if (empty($username) || empty($password)) {
            echo json_encode(array('success' => false, 'message' => 'Username and password are required'));
            return;
        }

        $user_data = array(
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'full_name' => $full_name,
            'role' => $role
        );

        $user_id = $this->User_model->create($user_data);

        if ($user_id) {
            echo json_encode(array('success' => true, 'message' => 'User created successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to create user'));
        }
    }

    /**
     * Update user (AJAX)
     */
    public function update_user($id) {
        header('Content-Type: application/json');

        // Admin only
        if (!$this->auth->is_admin()) {
            echo json_encode(array('success' => false, 'message' => 'Permission denied'));
            return;
        }

        $user_data = array(
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'full_name' => $this->input->post('full_name'),
            'role' => $this->input->post('role'),
            'is_active' => $this->input->post('is_active')
        );

        // Only update password if provided
        $password = $this->input->post('password');
        if (!empty($password)) {
            $user_data['password'] = $password;
        }

        if ($this->User_model->update($id, $user_data)) {
            echo json_encode(array('success' => true, 'message' => 'User updated successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to update user'));
        }
    }

    /**
     * Delete user (AJAX)
     */
    public function delete_user($id) {
        header('Content-Type: application/json');

        // Admin only
        if (!$this->auth->is_admin()) {
            echo json_encode(array('success' => false, 'message' => 'Permission denied'));
            return;
        }

        // Don't allow deleting yourself
        if ($id == $this->auth->user_id()) {
            echo json_encode(array('success' => false, 'message' => 'You cannot delete your own account'));
            return;
        }

        if ($this->User_model->delete($id)) {
            echo json_encode(array('success' => true, 'message' => 'User deleted successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete user. Cannot delete the last admin.'));
        }
    }
}
