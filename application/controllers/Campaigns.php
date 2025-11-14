<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaigns extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('ari_client');
        $this->load->model('Campaign_model');
        $this->load->model('Campaign_number_model');
        $this->load->model('Ivr_menu_model');
        $this->load->library('upload');
    }

    /**
     * List all campaigns
     */
    public function index() {
        $data['campaigns'] = $this->Campaign_model->get_all();

        // Get stats for each campaign
        foreach ($data['campaigns'] as $campaign) {
            $campaign->stats = $this->Campaign_model->get_stats($campaign->id);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('campaigns/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * View campaign details
     */
    public function view($id) {
        $data['campaign'] = $this->Campaign_model->get_by_id($id);

        if (!$data['campaign']) {
            show_404();
        }

        $data['stats'] = $this->Campaign_model->get_stats($id);
        $data['numbers'] = $this->Campaign_number_model->get_by_campaign($id, null, 100);

        $this->load->view('templates/header', $data);
        $this->load->view('campaigns/view', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Add new campaign
     */
    public function add() {
        if ($this->input->post()) {
            $campaign_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'trunk_type' => $this->input->post('trunk_type'),
                'trunk_value' => $this->input->post('trunk_value'),
                'callerid' => $this->input->post('callerid'),
                'agent_dest_type' => $this->input->post('agent_dest_type'),
                'agent_dest_value' => $this->input->post('agent_dest_value'),
                'record_calls' => $this->input->post('record_calls') ? 1 : 0,
                'concurrent_calls' => $this->input->post('concurrent_calls'),
                'retry_times' => $this->input->post('retry_times'),
                'retry_delay' => $this->input->post('retry_delay')
            );

            $campaign_id = $this->Campaign_model->create($campaign_data);

            if ($campaign_id) {
                $this->session->set_flashdata('success', 'Campaign created successfully');
                redirect('campaigns/view/' . $campaign_id);
            } else {
                $this->session->set_flashdata('error', 'Failed to create campaign');
            }
        }

        // Get available endpoints for trunk selection
        $endpoints_result = $this->ari_client->get_endpoints();
        $data['endpoints'] = $endpoints_result['success'] && is_array($endpoints_result['data']) ? $endpoints_result['data'] : array();

        // Get IVR menus for agent destination
        $data['ivr_menus'] = $this->Ivr_menu_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('campaigns/form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Edit campaign
     */
    public function edit($id) {
        $data['campaign'] = $this->Campaign_model->get_by_id($id);

        if (!$data['campaign']) {
            show_404();
        }

        if ($this->input->post()) {
            $campaign_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'trunk_type' => $this->input->post('trunk_type'),
                'trunk_value' => $this->input->post('trunk_value'),
                'callerid' => $this->input->post('callerid'),
                'agent_dest_type' => $this->input->post('agent_dest_type'),
                'agent_dest_value' => $this->input->post('agent_dest_value'),
                'record_calls' => $this->input->post('record_calls') ? 1 : 0,
                'concurrent_calls' => $this->input->post('concurrent_calls'),
                'retry_times' => $this->input->post('retry_times'),
                'retry_delay' => $this->input->post('retry_delay')
            );

            if ($this->Campaign_model->update($id, $campaign_data)) {
                $this->session->set_flashdata('success', 'Campaign updated successfully');
                redirect('campaigns/view/' . $id);
            } else {
                $this->session->set_flashdata('error', 'Failed to update campaign');
            }
        }

        // Get available endpoints for trunk selection
        $endpoints_result = $this->ari_client->get_endpoints();
        $data['endpoints'] = $endpoints_result['success'] && is_array($endpoints_result['data']) ? $endpoints_result['data'] : array();

        // Get IVR menus for agent destination
        $data['ivr_menus'] = $this->Ivr_menu_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('campaigns/form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Delete campaign
     */
    public function delete($id) {
        $campaign = $this->Campaign_model->get_by_id($id);

        if (!$campaign) {
            show_404();
        }

        // Don't allow deletion of running campaigns
        if ($campaign->status == 'running') {
            $this->session->set_flashdata('error', 'Cannot delete a running campaign. Stop it first.');
            redirect('campaigns');
        }

        if ($this->Campaign_model->delete($id)) {
            $this->session->set_flashdata('success', 'Campaign deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete campaign');
        }

        redirect('campaigns');
    }

    /**
     * Campaign control - Start, Stop, Pause
     */
    public function control($id, $action) {
        $campaign = $this->Campaign_model->get_by_id($id);

        if (!$campaign) {
            echo json_encode(array('success' => false, 'message' => 'Campaign not found'));
            return;
        }

        $valid_actions = array('start', 'stop', 'pause');
        if (!in_array($action, $valid_actions)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid action'));
            return;
        }

        $status_map = array(
            'start' => 'running',
            'stop' => 'stopped',
            'pause' => 'paused'
        );

        if ($this->Campaign_model->update_status($id, $status_map[$action])) {
            // If stopping, reset all numbers to pending status
            if ($action === 'stop') {
                $this->db->where('campaign_id', $id);
                $this->db->update('campaign_numbers', array(
                    'status' => 'pending',
                    'attempts' => 0,
                    'last_attempt' => NULL
                ));
            }

            // Notify Node.js application via webhook/signal
            $this->notify_campaign_control($id, $action);

            echo json_encode(array('success' => true, 'message' => 'Campaign ' . $action . 'ed successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to ' . $action . ' campaign'));
        }
    }

    /**
     * Upload CSV with numbers
     */
    public function upload_numbers($campaign_id) {
        $campaign = $this->Campaign_model->get_by_id($campaign_id);

        if (!$campaign) {
            $this->session->set_flashdata('error', 'Campaign not found');
            redirect('campaigns');
        }

        if ($this->input->post()) {
            $config['upload_path'] = APPPATH . '../uploads/';
            $config['allowed_types'] = 'csv|txt';
            $config['max_size'] = 10240; // 10MB

            $this->upload->initialize($config);

            if ($this->upload->do_upload('csv_file')) {
                $file_data = $this->upload->data();
                $file_path = $file_data['full_path'];

                // Parse CSV
                $numbers = array();
                if (($handle = fopen($file_path, 'r')) !== FALSE) {
                    // Check if first row is header
                    $first_row = fgetcsv($handle);
                    $has_header = false;
                    if ($first_row && (strtolower($first_row[0]) == 'number' || strtolower($first_row[0]) == 'phone')) {
                        $has_header = true;
                    } else {
                        // Not a header, process it as data
                        if (!empty($first_row[0])) {
                            $data = null;
                            if (isset($first_row[1]) && !empty($first_row[1])) {
                                $data = array('name' => $first_row[1]);
                            }
                            $numbers[] = array(
                                'phone' => $first_row[0],
                                'data' => $data
                            );
                        }
                    }

                    // Process remaining rows
                    while (($row = fgetcsv($handle)) !== FALSE) {
                        if (!empty($row[0])) {
                            $data = null;
                            if (isset($row[1]) && !empty($row[1])) {
                                $data = array('name' => $row[1]);
                            }
                            $numbers[] = array(
                                'phone' => $row[0],
                                'data' => $data
                            );
                        }
                    }
                    fclose($handle);
                }

                // Insert numbers
                if (!empty($numbers)) {
                    $this->Campaign_number_model->bulk_add($campaign_id, $numbers);
                    $this->session->set_flashdata('success', count($numbers) . ' numbers imported successfully');
                } else {
                    $this->session->set_flashdata('error', 'No valid numbers found in CSV');
                }

                // Delete uploaded file
                unlink($file_path);
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            }

            redirect('campaigns/view/' . $campaign_id);
        }
    }

    /**
     * Add single number manually (deprecated - use add_numbers_bulk)
     */
    public function add_number($campaign_id) {
        header('Content-Type: application/json');

        $phone_number = $this->input->post('phone_number');

        if (empty($phone_number)) {
            echo json_encode(array('success' => false, 'message' => 'Phone number is required'));
            return;
        }

        if ($this->Campaign_number_model->add_number($campaign_id, $phone_number)) {
            echo json_encode(array('success' => true, 'message' => 'Number added successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to add number'));
        }
    }

    /**
     * Add numbers in bulk (number,name format)
     */
    public function add_numbers_bulk($campaign_id) {
        header('Content-Type: application/json');

        $numbers_bulk = $this->input->post('numbers_bulk');

        if (empty($numbers_bulk)) {
            echo json_encode(array('success' => false, 'message' => 'Numbers are required'));
            return;
        }

        $lines = explode("\n", $numbers_bulk);
        $added = 0;
        $errors = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parse line: number,name (name is optional)
            $parts = array_map('trim', explode(',', $line, 2));
            $phone = $parts[0];
            $name = isset($parts[1]) ? $parts[1] : '';

            if (empty($phone)) continue;

            // Prepare data
            $data = null;
            if (!empty($name)) {
                $data = array('name' => $name);
            }

            // Add number
            if ($this->Campaign_number_model->add_number($campaign_id, $phone, $data)) {
                $added++;
            } else {
                $errors[] = $phone;
            }
        }

        if ($added > 0) {
            $message = "$added number(s) added successfully";
            if (count($errors) > 0) {
                $message .= ". Failed to add: " . implode(', ', $errors);
            }
            echo json_encode(array('success' => true, 'message' => $message));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to add any numbers'));
        }
    }

    /**
     * Delete number from campaign
     */
    public function delete_number($number_id) {
        header('Content-Type: application/json');

        if ($this->Campaign_number_model->delete($number_id)) {
            echo json_encode(array('success' => true, 'message' => 'Number deleted successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete number'));
        }
    }

    /**
     * Notify Node.js application about campaign control
     */
    private function notify_campaign_control($campaign_id, $action) {
        // This could be a webhook, Redis pub/sub, or database flag
        // For now, we'll use a simple flag in the database
        // The Node.js application will poll this or listen via WebSocket
    }
}
