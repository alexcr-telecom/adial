<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('ari_client');
        $this->load->model('Campaign_model');
        $this->load->model('Cdr_model');
    }

    public function index() {
        $data = array();

        // Get Asterisk info
        $asterisk_info = $this->ari_client->get_asterisk_info();
        $data['asterisk_status'] = $asterisk_info['success'] ? 'online' : 'offline';
        $data['asterisk_info'] = $asterisk_info['success'] ? $asterisk_info['data'] : null;

        // Check database connection
        try {
            $this->db->query('SELECT 1');
            $data['database_status'] = 'online';
        } catch (Exception $e) {
            $data['database_status'] = 'offline';
        }

        // Check ARI WebSocket (based on config)
        $data['ari_ws_status'] = $asterisk_info['success'] ? 'online' : 'offline';

        // Get campaigns
        $data['campaigns'] = $this->Campaign_model->get_all();
        $data['active_campaigns'] = $this->Campaign_model->get_active();

        // Get active channels
        $channels = $this->ari_client->get_channels();
        $data['active_channels'] = $channels['success'] ? (is_array($channels['data']) ? count($channels['data']) : 0) : 0;
        $data['channels_list'] = $channels['success'] && is_array($channels['data']) ? $channels['data'] : array();

        // Get recent CDR stats
        $data['today_calls'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                        ->count_all_results('cdr');

        $data['today_answered'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                           ->where('disposition', 'answered')
                                           ->count_all_results('cdr');

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * AJAX: Get system status
     */
    public function get_status() {
        header('Content-Type: application/json');

        $status = array();

        // Get Asterisk info
        $asterisk_info = $this->ari_client->get_asterisk_info();
        $status['asterisk'] = $asterisk_info['success'] ? 'online' : 'offline';

        // Database status
        try {
            $this->db->query('SELECT 1');
            $status['database'] = 'online';
        } catch (Exception $e) {
            $status['database'] = 'offline';
        }

        // Active channels
        $channels = $this->ari_client->get_channels();
        $status['active_channels'] = $channels['success'] && is_array($channels['data']) ? count($channels['data']) : 0;

        // Active campaigns
        $status['active_campaigns'] = $this->db->where('status', 'running')
                                               ->count_all_results('campaigns');

        echo json_encode($status);
    }

    /**
     * AJAX: Get active channels
     */
    public function get_channels() {
        header('Content-Type: application/json');

        $channels = $this->ari_client->get_channels();

        if ($channels['success'] && is_array($channels['data'])) {
            echo json_encode(array('success' => true, 'channels' => $channels['data']));
        } else {
            echo json_encode(array('success' => false, 'channels' => array()));
        }
    }
}
