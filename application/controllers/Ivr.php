<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ivr extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Ivr_menu_model');
        $this->load->model('Ivr_action_model');
        $this->load->model('Campaign_model');
        $this->load->library('upload');
    }

    /**
     * List all IVR menus
     */
    public function index() {
        $data['ivr_menus'] = $this->Ivr_menu_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('ivr/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * View IVR menu details
     */
    public function view($id) {
        $data['ivr_menu'] = $this->Ivr_menu_model->get_with_actions($id);

        if (!$data['ivr_menu']) {
            show_404();
        }

        // Get campaign info
        $data['campaign'] = $this->Campaign_model->get_by_id($data['ivr_menu']->campaign_id);

        $this->load->view('templates/header', $data);
        $this->load->view('ivr/view', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Add new IVR menu
     */
    public function add($campaign_id = null) {
        if ($this->input->post()) {
            // Handle audio file upload
            $audio_file = null;

            if (!empty($_FILES['audio_file']['name'])) {
                $audio_file = $this->upload_audio_file();

                if (!$audio_file) {
                    $this->session->set_flashdata('error', 'Failed to upload audio file: ' . $this->upload->display_errors('', ''));
                    redirect('ivr/add/' . $campaign_id);
                    return;
                }
            } else {
                $this->session->set_flashdata('error', 'Audio file is required');
                redirect('ivr/add/' . $campaign_id);
                return;
            }

            // Create IVR menu
            $menu_data = array(
                'campaign_id' => $this->input->post('campaign_id'),
                'name' => $this->input->post('name'),
                'audio_file' => $audio_file,
                'timeout' => $this->input->post('timeout'),
                'max_digits' => $this->input->post('max_digits')
            );

            $ivr_menu_id = $this->Ivr_menu_model->create($menu_data);

            if ($ivr_menu_id) {
                // Create IVR actions
                $this->save_actions($ivr_menu_id);

                $this->session->set_flashdata('success', 'IVR menu created successfully');
                redirect('ivr/view/' . $ivr_menu_id);
            } else {
                $this->session->set_flashdata('error', 'Failed to create IVR menu');
            }
        }

        // Get campaigns
        $data['campaigns'] = $this->Campaign_model->get_all();
        $data['campaign_id'] = $campaign_id;

        $this->load->view('templates/header', $data);
        $this->load->view('ivr/form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Edit IVR menu
     */
    public function edit($id) {
        $data['ivr_menu'] = $this->Ivr_menu_model->get_with_actions($id);

        if (!$data['ivr_menu']) {
            show_404();
        }

        if ($this->input->post()) {
            // Check if new audio file uploaded
            $audio_file = $data['ivr_menu']->audio_file;

            if (!empty($_FILES['audio_file']['name'])) {
                $new_audio_file = $this->upload_audio_file();

                if ($new_audio_file) {
                    // Delete old file
                    $old_file = $this->config->item('asterisk_sounds_dir') . $audio_file;
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }

                    $audio_file = $new_audio_file;
                }
            }

            // Update IVR menu
            $menu_data = array(
                'name' => $this->input->post('name'),
                'audio_file' => $audio_file,
                'timeout' => $this->input->post('timeout'),
                'max_digits' => $this->input->post('max_digits')
            );

            if ($this->Ivr_menu_model->update($id, $menu_data)) {
                // Delete old actions and create new ones
                $this->Ivr_action_model->delete_by_menu($id);
                $this->save_actions($id);

                $this->session->set_flashdata('success', 'IVR menu updated successfully');
                redirect('ivr/view/' . $id);
            } else {
                $this->session->set_flashdata('error', 'Failed to update IVR menu');
            }
        }

        // Get campaigns
        $data['campaigns'] = $this->Campaign_model->get_all();

        $this->load->view('templates/header', $data);
        $this->load->view('ivr/form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Delete IVR menu
     */
    public function delete($id) {
        $ivr_menu = $this->Ivr_menu_model->get_by_id($id);

        if (!$ivr_menu) {
            show_404();
        }

        // Delete audio file
        $audio_file = $this->config->item('asterisk_sounds_dir') . $ivr_menu->audio_file;
        if (file_exists($audio_file)) {
            unlink($audio_file);
        }

        // Delete IVR menu (actions will be deleted by CASCADE)
        if ($this->Ivr_menu_model->delete($id)) {
            $this->session->set_flashdata('success', 'IVR menu deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete IVR menu');
        }

        redirect('ivr');
    }

    /**
     * Upload and convert audio file
     */
    private function upload_audio_file() {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'wav|mp3';
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = 'ivr_' . time();

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('audio_file')) {
            return false;
        }

        $upload_data = $this->upload->data();
        $uploaded_file = $upload_data['full_path'];
        $file_extension = $upload_data['file_ext'];

        // Generate output filename
        $output_filename = 'ivr_' . time();
        $sounds_dir = $this->config->item('asterisk_sounds_dir');

        // Convert to asterisk format (wav, 8000Hz, mono)
        if ($file_extension == '.mp3') {
            // Convert MP3 to WAV
            $output_file = $sounds_dir . $output_filename . '.wav';
            $cmd = "ffmpeg -i " . escapeshellarg($uploaded_file) . " -ar 8000 -ac 1 -y " . escapeshellarg($output_file);

            exec($cmd, $output, $return_var);

            if ($return_var !== 0) {
                // Fallback to sox if ffmpeg fails
                $cmd = "sox " . escapeshellarg($uploaded_file) . " -r 8000 -c 1 " . escapeshellarg($output_file);
                exec($cmd, $output, $return_var);

                if ($return_var !== 0) {
                    unlink($uploaded_file);
                    return false;
                }
            }

            unlink($uploaded_file);
            return $output_filename . '.wav';

        } else {
            // Already WAV, just convert to proper format
            $output_file = $sounds_dir . $output_filename . '.wav';
            $cmd = "sox " . escapeshellarg($uploaded_file) . " -r 8000 -c 1 " . escapeshellarg($output_file);

            exec($cmd, $output, $return_var);

            if ($return_var !== 0) {
                // If sox fails, try ffmpeg
                $cmd = "ffmpeg -i " . escapeshellarg($uploaded_file) . " -ar 8000 -ac 1 -y " . escapeshellarg($output_file);
                exec($cmd, $output, $return_var);

                if ($return_var !== 0) {
                    unlink($uploaded_file);
                    return false;
                }
            }

            unlink($uploaded_file);
            return $output_filename . '.wav';
        }
    }

    /**
     * Save IVR actions from POST data
     */
    private function save_actions($ivr_menu_id) {
        $dtmf_digits = $this->input->post('dtmf_digit');
        $action_types = $this->input->post('action_type');
        $action_values = $this->input->post('action_value');

        if (empty($dtmf_digits)) {
            return;
        }

        foreach ($dtmf_digits as $index => $digit) {
            if (!empty($digit)) {
                $action_data = array(
                    'ivr_menu_id' => $ivr_menu_id,
                    'dtmf_digit' => $digit,
                    'action_type' => $action_types[$index],
                    'action_value' => $action_values[$index]
                );

                $this->Ivr_action_model->create($action_data);
            }
        }
    }

    /**
     * Serve audio file
     */
    public function audio($filename) {
        // Security: Only allow specific characters in filename
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.(wav|mp3|gsm)$/', $filename)) {
            show_404();
        }

        $file_path = '/var/lib/asterisk/sounds/dialer/' . $filename;

        if (!file_exists($file_path)) {
            show_404();
        }

        // Set headers
        header('Content-Type: audio/wav');
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Cache-Control: public, max-age=3600');

        // Output file
        readfile($file_path);
        exit;
    }
}
