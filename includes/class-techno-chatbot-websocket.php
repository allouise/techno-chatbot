<?php
/**
 * Websocket Class
 *
 * @package Techno_Chatbot
 */

class Techno_Chatbot_Websocket {

    /**
	 * Class instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Class    $instance    Class instance.
	 */
	private static $instance = null;

    /**
	 * Websocket host
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Websocket host
	 */
	private $host;

    /**
	 * Websocket port
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $plugin_name    Websocket port
	 */
	private $port;

    /**
	 * Websocket secret
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Websocket secret
	 */
	private $secret;

    /**
	 * Websocket endpoint_status
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Websocket endpoint_status
	 */
	private $endpoints = [];
    
    /**
	 * Constructor
	 *
	 * @since    1.0.0
	 */
    public function __construct() {
        $this->host = 'localhost';
        $this->port = 3000;
        $this->secret = get_option('techno_chatbot_secret', '');
        $this->endpoints = [
            'status' => '/status',
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Class instance
     * 
     * @since    1.0.0
     */
    public static function instance() {
        if ( self::$instance == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get URL
     * 
     * @since    1.0.0
     */
    public function get_url($endpoint_key = '') {
        $endpoint = ($endpoint_key && isset($this->endpoints[$endpoint_key])) 
            ? $this->endpoints[$endpoint_key] 
            : '';
        $scheme = is_ssl() ? 'https' : 'http';
        return "{$scheme}://{$this->host}:{$this->port}{$endpoint}";
        //return "http://{$this->host}:{$this->port}{$endpoint}"; /* Use http for now for test when websocket is setup in cpanel we will require SSL */
    }

    /**
     * Get Token
     * 
     * @since    1.0.0
     */
    public function get_token($site = '') {
        if (empty($site)) {
            $site = get_site_url();
        }
        return hash_hmac('sha256', $site, $this->secret);
    }

    /**
     * Check if WebSocket server is running
     * 
     * @since    1.0.0
     */
    public function is_running(): bool {
        $connection = @fsockopen($this->host, $this->port, $errno, $errstr, 1);
        if (!is_resource($connection)) return false;
        fclose($connection);

        $site  = get_site_url();
        $token = $this->get_token($site);
        $url = $this->get_url('status') . "?site=" . urlencode($site) . "&token=" . urlencode($token);
        $response = wp_remote_get($url, [
            'timeout' => 2,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code !== 200) return false;
        $data = json_decode($body, true);

        if (!is_array($data)) return false;

        return ( isset($data['running']) && $data['running'] === true );
    }

    /**
     * Force offline if WS is down
     */
    public function sync_status() {
        if (!$this->is_running()) {
            update_option('techno_chatbot_support_online', 0);
        }
    }

}