<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('session');
    }

    /**
     * Check if user is logged in
     */
    public function is_logged_in() {
        return $this->CI->session->userdata('user_id') !== null;
    }

    /**
     * Get current user ID
     */
    public function user_id() {
        return $this->CI->session->userdata('user_id');
    }

    /**
     * Get current user data
     */
    public function user() {
        if (!$this->is_logged_in()) {
            return null;
        }

        return (object) array(
            'id' => $this->CI->session->userdata('user_id'),
            'username' => $this->CI->session->userdata('username'),
            'email' => $this->CI->session->userdata('email'),
            'full_name' => $this->CI->session->userdata('full_name'),
            'role' => $this->CI->session->userdata('role')
        );
    }

    /**
     * Check if user is admin
     */
    public function is_admin() {
        return $this->CI->session->userdata('role') === 'admin';
    }

    /**
     * Login user
     */
    public function login($user) {
        $session_data = array(
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'logged_in' => true
        );

        $this->CI->session->set_userdata($session_data);
        return true;
    }

    /**
     * Logout user
     */
    public function logout() {
        $this->CI->session->unset_userdata('user_id');
        $this->CI->session->unset_userdata('username');
        $this->CI->session->unset_userdata('email');
        $this->CI->session->unset_userdata('full_name');
        $this->CI->session->unset_userdata('role');
        $this->CI->session->unset_userdata('logged_in');
        $this->CI->session->sess_destroy();
        return true;
    }

    /**
     * Require login - redirect to login page if not logged in
     */
    public function require_login() {
        if (!$this->is_logged_in()) {
            redirect('login');
        }
    }

    /**
     * Require admin - redirect if not admin
     */
    public function require_admin() {
        $this->require_login();

        if (!$this->is_admin()) {
            $this->CI->session->set_flashdata('error', 'You do not have permission to access this page');
            redirect('dashboard');
        }
    }
}
