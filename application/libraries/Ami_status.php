<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AMI Status Library
 * Gets Asterisk status information via AMI
 */
class Ami_status {

    private $CI;
    private $ami_host;
    private $ami_port;
    private $ami_username;
    private $ami_password;

    public function __construct() {
        $this->CI =& get_instance();

        // Load AMI configuration from daemon config
        $daemon_config = require FCPATH . 'ami-daemon/config.php';

        $this->ami_host = $daemon_config['ami']['host'];
        $this->ami_port = $daemon_config['ami']['port'];
        $this->ami_username = $daemon_config['ami']['username'];
        $this->ami_password = $daemon_config['ami']['password'];
    }

    /**
     * Check AMI connection status
     */
    public function get_status() {
        try {
            $socket = $this->connect_ami();
            if (!$socket) {
                return [
                    'success' => false,
                    'status' => 'offline',
                    'error' => 'Failed to connect to AMI'
                ];
            }

            $this->ami_login($socket);

            // Get Asterisk version
            $version = $this->get_asterisk_version($socket);

            // Get system info
            $uptime = $this->get_system_uptime($socket);

            fclose($socket);

            return [
                'success' => true,
                'status' => 'online',
                'version' => $version,
                'uptime' => $uptime
            ];

        } catch (Exception $e) {
            log_message('error', 'AMI Status error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get active channels count
     */
    public function get_active_channels_count() {
        try {
            $socket = $this->connect_ami();
            if (!$socket) {
                return 0;
            }

            $this->ami_login($socket);

            // Send CoreShowChannels action
            $this->send_action($socket, [
                'Action' => 'CoreShowChannels'
            ]);

            $response = $this->read_response($socket);
            fclose($socket);

            // Parse channel count from response
            if (preg_match('/(\d+)\s+active channels?/i', $response, $matches)) {
                return (int)$matches[1];
            }

            return 0;

        } catch (Exception $e) {
            log_message('error', 'AMI channels count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active channels list
     */
    public function get_active_channels() {
        try {
            $socket = $this->connect_ami();
            if (!$socket) {
                return [];
            }

            $this->ami_login($socket);

            // Send CoreShowChannels action
            $this->send_action($socket, [
                'Action' => 'CoreShowChannels'
            ]);

            $response = $this->read_response($socket);
            fclose($socket);

            // Parse channels from response
            $channels = [];
            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                if (preg_match('/^Event:\s+CoreShowChannel/i', $line)) {
                    $channel = [];
                    // Read channel details
                    while ($line = array_shift($lines)) {
                        if (trim($line) === '') break;
                        if (preg_match('/^(\w+):\s*(.+)$/i', $line, $matches)) {
                            $channel[strtolower($matches[1])] = trim($matches[2]);
                        }
                    }
                    if (!empty($channel)) {
                        $channels[] = $channel;
                    }
                }
            }

            return $channels;

        } catch (Exception $e) {
            log_message('error', 'AMI channels list error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Asterisk version
     */
    private function get_asterisk_version($socket) {
        $this->send_action($socket, [
            'Action' => 'Command',
            'Command' => 'core show version'
        ]);

        $response = $this->read_command_response($socket);

        if (preg_match('/Asterisk\s+([\d\.]+)/i', $response, $matches)) {
            return $matches[1];
        }

        return 'Unknown';
    }

    /**
     * Get system uptime
     */
    private function get_system_uptime($socket) {
        $this->send_action($socket, [
            'Action' => 'Command',
            'Command' => 'core show uptime'
        ]);

        $response = $this->read_command_response($socket);

        if (preg_match('/System uptime:\s*(.+)/i', $response, $matches)) {
            return trim($matches[1]);
        }

        return 'Unknown';
    }

    /**
     * Connect to AMI
     */
    private function connect_ami() {
        $socket = @fsockopen($this->ami_host, $this->ami_port, $errno, $errstr, 5);

        if (!$socket) {
            log_message('error', "AMI Status: Failed to connect to AMI: $errstr ($errno)");
            return false;
        }

        stream_set_blocking($socket, true);
        stream_set_timeout($socket, 5);

        // Read welcome message
        $welcome = '';
        $timeout = time() + 5;
        while (time() < $timeout) {
            $line = fgets($socket);
            if ($line === false) break;
            $welcome .= $line;
            if (strpos($welcome, "\r\n\r\n") !== false) break;
        }

        return $socket;
    }

    /**
     * Login to AMI
     */
    private function ami_login($socket) {
        $this->send_action($socket, [
            'Action' => 'Login',
            'Username' => $this->ami_username,
            'Secret' => $this->ami_password
        ]);

        $response = '';
        $timeout = time() + 5;
        while (time() < $timeout) {
            $line = fgets($socket);
            if ($line === false) break;
            $response .= $line;
            if (strpos($response, "\r\n\r\n") !== false) break;
        }

        if (stripos($response, 'Success') === false) {
            throw new Exception("AMI login failed");
        }
    }

    /**
     * Send AMI action
     */
    private function send_action($socket, $params) {
        $message = '';
        foreach ($params as $key => $value) {
            $message .= "$key: $value\r\n";
        }
        $message .= "\r\n";

        fwrite($socket, $message);
    }

    /**
     * Read standard response
     */
    private function read_response($socket) {
        $response = '';
        $timeout = time() + 5;

        while (time() < $timeout) {
            $line = fgets($socket);
            if ($line === false) {
                usleep(10000);
                continue;
            }

            $response .= $line;

            if (strpos($response, "\r\n\r\n") !== false) {
                break;
            }
        }

        return $response;
    }

    /**
     * Read command response (waits for multiple empty lines)
     */
    private function read_command_response($socket) {
        $response = '';
        $timeout = time() + 5;
        $emptyLines = 0;

        while (time() < $timeout) {
            $line = fgets($socket);
            if ($line === false) {
                usleep(10000);
                continue;
            }

            $response .= $line;

            if (trim($line) === '') {
                $emptyLines++;
                if ($emptyLines >= 3) {
                    break;
                }
            } else {
                $emptyLines = 0;
            }
        }

        return $response;
    }
}
