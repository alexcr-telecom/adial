<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('ami_status');
        $this->load->model('Campaign_model');
        $this->load->model('Cdr_model');
    }

    public function index() {
        $data = array();

        // Get Asterisk info via AMI
        $asterisk_info = $this->ami_status->get_status();
        $data['asterisk_status'] = $asterisk_info['status'];
        $data['asterisk_info'] = $asterisk_info;

        // Check database connection
        try {
            $this->db->query('SELECT 1');
            $data['database_status'] = 'online';
        } catch (Exception $e) {
            $data['database_status'] = 'offline';
        }

        // Check AMI connection status
        $data['ami_status'] = $asterisk_info['status'];

        // Get campaigns
        $data['campaigns'] = $this->Campaign_model->get_all();
        $data['active_campaigns'] = $this->Campaign_model->get_active();

        // Get active channels via AMI
        $data['active_channels'] = $this->ami_status->get_active_channels_count();
        $data['channels_list'] = $this->ami_status->get_active_channels();

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

        // Get Asterisk info via AMI
        $asterisk_info = $this->ami_status->get_status();
        $status['asterisk'] = $asterisk_info['status'];
        $status['ami'] = $asterisk_info['status'];

        // Database status
        try {
            $this->db->query('SELECT 1');
            $status['database'] = 'online';
        } catch (Exception $e) {
            $status['database'] = 'offline';
        }

        // Active channels via AMI
        $status['active_channels'] = $this->ami_status->get_active_channels_count();

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

        $channels = $this->ami_status->get_active_channels();

        if (!empty($channels)) {
            echo json_encode(array('success' => true, 'channels' => $channels));
        } else {
            echo json_encode(array('success' => false, 'channels' => array()));
        }
    }
}
