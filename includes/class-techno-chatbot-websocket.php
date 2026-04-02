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
	 * Websocket status_url
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Websocket status_url
	 */
	private $status_url;
    
    /**
	 * Constructor
	 *
	 * @since    1.0.0
	 */
    public function __construct() {
        $this->host = 'localhost';
        $this->port = 3000;
        $this->secret = 'nH3Vdn0bvVpuQ1QLhqnH75yMBTn2uXdK';
        $this->status_url = 'http://localhost:3000/status';
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialize
     */
    /* private function __wakeup() {} */

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
     * Check if WebSocket server is running
     */
    public function is_running(): bool {
        $connection = @fsockopen($this->host, $this->port, $errno, $errstr, 1);
        if (!is_resource($connection)) {
            return false; 
        }
        fclose($connection);

        $site = get_site_url();
        $token = hash_hmac('sha256', $site, $this->secret);
        $url = "{$this->status_url}?site=" . urlencode($site) . "&token=" . urlencode($token);
        $res = @file_get_contents($url);
        if (!$res) return false;

        return true;
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