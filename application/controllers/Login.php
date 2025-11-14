<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('auth');
        $this->load->model('User_model');
    }

    /**
     * Login page
     */
    public function index() {
        // If already logged in, redirect to dashboard
        if ($this->auth->is_logged_in()) {
            redirect('dashboard');
        }

        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $user = $this->User_model->verify_login($username, $password);

            if ($user) {
                // Update last login
                $this->User_model->update_last_login($user->id);

                // Login user
                $this->auth->login($user);

                $this->session->set_flashdata('success', 'Welcome back, ' . $user->full_name . '!');
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid username or password');
            }
        }

        $this->load->view('login/index');
    }

    /**
     * Logout
     */
    public function logout() {
        $this->auth->logout();
        $this->session->set_flashdata('success', 'You have been logged out successfully');
        redirect('login');
    }
}
