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
	 * Websocket URL
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Websocket url
	 */
	private $api_url;

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
        $this->api_url = trailingslashit(get_option('techno_chatbot_apiurl', ''));
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
        $base = rtrim(get_option('techno_chatbot_apiurl', ''), '/');
        if (empty($base)) {
            return '';
        }

        $endpoint = ($endpoint_key && isset($this->endpoints[$endpoint_key])) ? $this->endpoints[$endpoint_key] : '';
        return $base . $endpoint;
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
        $site  = get_site_url();
        $token = $this->get_token($site);
        $url = $this->get_url('status') . "?site=" . urlencode($site) . "&token=" . urlencode($token);
        $response = wp_remote_get($url, [
            'timeout' => 2,
            'sslverify' => false, // ← needed for local self-signed/mkcert certs
        ]);

        if (is_wp_error($response)) return false;

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