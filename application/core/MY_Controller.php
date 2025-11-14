<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller - All controllers should extend this
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('auth');
        $this->load->helper('cookie');

        // Set up language
        $this->_set_language();

        // Define public methods that don't require authentication
        $public_methods = array(
            'ivr' => array('audio')
        );

        // Check if current method is public
        $is_public = false;
        if (isset($public_methods[$this->router->class])) {
            $is_public = in_array($this->router->method, $public_methods[$this->router->class]);
        }

        // Require login for all pages except login and public methods
        if (!in_array($this->router->class, array('login')) && !$is_public) {
            $this->auth->require_login();
        }
    }

    /**
     * Set language based on user preference
     */
    private function _set_language() {
        // Check session first
        $lang = $this->session->userdata('site_lang');

        // If not in session, check cookie
        if (!$lang) {
            $lang = get_cookie('site_lang');
        }

        // Default to english
        if (!$lang || !in_array($lang, array('english', 'russian'))) {
            $lang = 'english';
        }

        // Set language
        $this->config->set_item('language', $lang);

        // Load language files
        $this->lang->load('common', $lang);
        $this->lang->load('navigation', $lang);
        $this->lang->load('campaigns', $lang);
        $this->lang->load('cdr', $lang);
        $this->lang->load('ivr', $lang);
        $this->lang->load('dashboard', $lang);
        $this->lang->load('settings', $lang);
        $this->lang->load('monitoring', $lang);
    }
}

/**
 * Admin Controller - For admin-only pages
 */
class Admin_Controller extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->auth->require_admin();
    }
}
