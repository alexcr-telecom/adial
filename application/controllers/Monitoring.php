<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('ari_client');
        $this->load->model('Campaign_model');
        $this->load->model('Cdr_model');
    }

    /**
     * Real-time monitoring page
     */
    public function index() {
        $data = array();

        // Get active campaigns
        $data['active_campaigns'] = $this->Campaign_model->get_active();

        // Get active channels from ARI
        $channels_result = $this->ari_client->get_channels();
        $data['active_channels'] = $channels_result['success'] && is_array($channels_result['data']) ? $channels_result['data'] : array();

        // Get today's stats
        $data['today_stats'] = $this->get_today_stats();

        $this->load->view('templates/header', $data);
        $this->load->view('monitoring/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Get real-time data (AJAX)
     */
    public function get_realtime_data() {
        header('Content-Type: application/json');

        $data = array();

        // Active campaigns
        $active_campaigns = $this->Campaign_model->get_active();
        $data['campaigns'] = array();

        foreach ($active_campaigns as $campaign) {
            $stats = $this->Campaign_model->get_stats($campaign->id);
            $data['campaigns'][] = array(
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'concurrent_calls' => $campaign->concurrent_calls,
                'stats' => $stats
            );
        }

        // Active channels
        $channels_result = $this->ari_client->get_channels();
        $data['channels'] = $channels_result['success'] && is_array($channels_result['data']) ? $channels_result['data'] : array();
        $data['channel_count'] = count($data['channels']);

        // Today's stats
        $data['today_stats'] = $this->get_today_stats();

        // System status
        $asterisk_info = $this->ari_client->get_asterisk_info();
        $data['asterisk_status'] = $asterisk_info['success'] ? 'online' : 'offline';

        echo json_encode($data);
    }

    /**
     * Get today's statistics
     */
    private function get_today_stats() {
        $stats = array();

        // Total calls today
        $stats['total_calls'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                         ->count_all_results('cdr');

        // Answered calls
        $stats['answered_calls'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                            ->where('disposition', 'answered')
                                            ->count_all_results('cdr');

        // Failed calls
        $stats['failed_calls'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                          ->where('disposition', 'failed')
                                          ->count_all_results('cdr');

        // No answer calls
        $stats['no_answer_calls'] = $this->db->where('DATE(start_time)', date('Y-m-d'))
                                             ->where('disposition', 'no_answer')
                                             ->count_all_results('cdr');

        // Answer rate
        $stats['answer_rate'] = $stats['total_calls'] > 0 ?
            round(($stats['answered_calls'] / $stats['total_calls']) * 100, 2) : 0;

        // Average talk time
        $query = $this->db->select('AVG(billsec) as avg_billsec')
                          ->where('DATE(start_time)', date('Y-m-d'))
                          ->where('disposition', 'answered')
                          ->get('cdr');

        $result = $query->row();
        $stats['avg_talk_time'] = $result ? round($result->avg_billsec) : 0;

        return $stats;
    }

    /**
     * Get campaign details (AJAX)
     */
    public function get_campaign_details($campaign_id) {
        header('Content-Type: application/json');

        $campaign = $this->Campaign_model->get_by_id($campaign_id);

        if (!$campaign) {
            echo json_encode(array('success' => false, 'message' => 'Campaign not found'));
            return;
        }

        $stats = $this->Campaign_model->get_stats($campaign_id);

        $data = array(
            'success' => true,
            'campaign' => $campaign,
            'stats' => $stats
        );

        echo json_encode($data);
    }
}
