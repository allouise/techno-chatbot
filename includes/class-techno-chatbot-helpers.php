<?php

/**
 * License Helper
 */
function techno_chatbot_license() {
    return Techno_Chatbot_License_Manager::instance();
}

function techno_chatbot_feature($features){
    if (techno_chatbot_license()->has_feature($features)) {
        return [
            'allowed' => true,
            'message' => ''
        ];
    }

    return [
        'allowed' => false,
        'message' => techno_chatbot_license()->get_upgrade_message($features)
    ];
}

function techno_chatbot_msgformat($msg, $type = 'error'){
    switch ($type) {
        case 'error':
            echo "<p style='color:red; font-weight:bold;'>$msg</p>";
        break;
    }
}

function techno_chatbot_get_ailimit() {
    $license_data = (array) get_option( 'techno_chatbot_license_data', [] );
    if ( empty( $license_data ) ) {
        return 0;
    }

    $allowed_limit = ! empty( $license_data['ai_assistance_limit'] ) ? (int) $license_data['ai_assistance_limit'] : 0;
    if ( $allowed_limit <= 0 ) {
        return 0;
    }

    $start_date = ! empty( $license_data['ailimit_start_date'] ) ? $license_data['ailimit_start_date'] : '';
    $end_date   = ! empty( $license_data['ailimit_end_date'] )   ? $license_data['ailimit_end_date']   : '';

    if ( empty( $start_date ) || empty( $end_date ) ) {
        return 0;
    }

    // Ensure the end date is valid and hasn't expired
    $end_timestamp = strtotime( $end_date );
    if ( ! $end_timestamp || $end_timestamp < current_time( 'timestamp' ) ) {
        return 0;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'techno_cb_messages';

    // Properly parameterized SQL query using %s placeholders
    $count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE (prompt_tokens > 0 OR completion_tokens > 0) AND created_at BETWEEN %s AND %s",
            $start_date,
            $end_date
        )
    );

    $remaining = $allowed_limit - $count;
    return max( 0, $remaining );
}

/**
 * Websocket Helper
 */
function techno_chatbot_websocket() {
    return Techno_Chatbot_Websocket::instance();
}

function techno_wss_check(){
    return techno_chatbot_websocket()->is_running();
}
