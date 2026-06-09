<?php
/**
 * License Manager
 *
 * @package Techno_Chatbot
 */

class Techno_Chatbot_License_Manager {

    /**
	 * Class instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Class    $instance    Class instance.
	 */
	private static $instance = null;

    /**
	 * Plugin license meta key
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_name    Option name for license key
	 */
    private $option_name = 'techno_chatbot_license';

	/**
	 * Plugin plans
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_plans    Plugin plans array
	 */
    private $plugin_plans = [ 
		'free' => [ 'label' => 'Free', 'url' => '#', 'features' => [ 
			'basic_chat' => 'Basic Chat'
		] ], 
		'master' => [ 'label' => 'Master', 'url' => '#', 'features' => [
			'basic_chat' => 'Basic Chat',
			'chat_history' => 'Chat History',
			'live_chat' => 'Live Chat',
			'ai_training' => 'Ai Training'
		] ] ];

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
     * Get license data
     * 
     * @since    1.0.0
     */
    public function get_license() {
        return get_option($this->option_name, []);
    }

    /**
     * Get current plan
     * 
     * @since    1.0.0
     */
    public function get_plan(){
        $license = get_option('techno_chatbot_license_data', []);
        if( empty($license['status']) || $license['status'] !== 'active' ){
            return 'free';
        }
        return $license['plan'] ?? 'free';
    }

    /**
     * Check if feature is allowed
     * 
     * @since    1.0.0
     */
    public function has_feature($feature, $match = 'any') {
		$features = $this->get_plan_features();
		if (!is_array($feature)) {
			return array_key_exists($feature, $features);
		}
		if ($match === 'all') {
			foreach ($feature as $f) {
				if (!array_key_exists($f, $features)) {
					return false;
				}
			}
			return true;
		}
		foreach ($feature as $f) {
			if (array_key_exists($f, $features)) {
				return true;
			}
		}
		return false;
	}

    /**
	 * Validate License
	 *
	 * @since    1.0.0
	 */
	public function validate_license( $license_key ) {

		$license_key = sanitize_text_field($license_key);
		$license_key = preg_replace('/[^A-Z0-9\-]/','', strtoupper($license_key));

		if (empty($license_key)) {
			update_option('techno_chatbot_license_data', [
				'plan'   => 'free',
				'status' => 'inactive'
			], false);
			return '';
		}

		$cache_key = 'tpl_license_' . md5($license_key);
		$cached = get_transient($cache_key);
		if ($cached !== false) {
			update_option('techno_chatbot_license_data', $cached, false);
			return $license_key;
		}

		$response = wp_remote_get(
			add_query_arg(
				[
					'license' => $license_key,
					'site' => home_url(),
				],
				'https://technodreamwebdesign.com/wp-json/techno-licensing/v1/check'
			),
			[
				'timeout' => 15,
			]
		);
		if (is_wp_error($response)) {
			return $license_key;
		}

		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (!is_array($data)) {
			$data = [];
		}

		if ($code !== 200 || !empty($data['error'])) {
			$license_data = [
				'key'    => $license_key,
				'plan'   => 'free',
				'status' => 'invalid',
				'expires'=> '',
				'last_check' => time()
			];
			update_option('techno_chatbot_license_data', $license_data, false);
			delete_transient($cache_key);
			return $license_key;
		}

		$status  = sanitize_text_field($data['status'] ?? 'invalid');
		$plan    = sanitize_text_field($data['plan'] ?? 'free');
		$expires = sanitize_text_field($data['expires'] ?? '');

		if ($status !== 'active') {
			$plan = 'free';
		}

		$license_data = [
			'key'        => $license_key,
			'plan'       => $plan,
			'status'     => $status,
			'expires'    => $expires,
			'last_check' => time()
		];

		update_option('techno_chatbot_license_data', $license_data, false);
		set_transient($cache_key, $license_data, 6 * HOUR_IN_SECONDS);
		return $license_key;
	}

	/**
	 * Get plan data
	 */
	private function get_plan_data($plan = null) {
		$plan = $plan ?: $this->get_plan();
		return $this->plugin_plans[$plan] ?? $this->plugin_plans['free'];
	}

	/**
	 * Get features of current plan
	 */
	private function get_plan_features($plan = null) {
		$plan_data = $this->get_plan_data($plan);
		return $plan_data['features'] ?? [];
	}

	/**
	 * Get upgrade message for a feature
	 */
	public function get_upgrade_message($feature) {
		if (is_array($feature)) {
			$feature = reset($feature);
		}
		$current_plan = $this->get_plan();
		foreach ($this->plugin_plans as $plan_key => $plan_data) {
			if (array_key_exists($feature, $plan_data['features'])) {
				if ($plan_key === $current_plan) {
					return '';
				}
				return sprintf(
					'%s is available in the %s plan. Upgrade <a href="%s">here</a>.',
					$plan_data['features'][$feature],
					$plan_data['label'],
					$plan_data['url']
				);
			}
		}
		return '';
	}
}