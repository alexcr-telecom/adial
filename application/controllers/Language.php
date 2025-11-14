<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('cookie');
    }

    /**
     * Switch language
     */
    public function switch_lang($language = 'english') {
        // Available languages
        $available_languages = array('english', 'russian');

        // Validate language
        if (!in_array($language, $available_languages)) {
            $language = 'english';
        }

        // Save to session
        $this->session->set_userdata('site_lang', $language);

        // Also save to cookie for 30 days
        $cookie = array(
            'name'   => 'site_lang',
            'value'  => $language,
            'expire' => 2592000, // 30 days
            'path'   => '/',
            'secure' => FALSE
        );
        $this->input->set_cookie($cookie);

        // Redirect back to previous page
        $redirect_url = $this->input->server('HTTP_REFERER');
        if (!$redirect_url) {
            $redirect_url = base_url();
        }

        redirect($redirect_url);
    }
}
