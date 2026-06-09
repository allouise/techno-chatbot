<?php

/**
 * License Helper
 */
function techno_chatbot_license() {
    return Techno_Chatbot_License_Manager::instance();
}
function techno_chatbot_check_plan( $plan ){
    return techno_chatbot_license()->get_plan() === $plan;
}

function techno_chatbot_feature($features){
    // testing purposes
    /* return [
            'allowed' => true,
            'message' => ''
        ];
    */
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

/**
 * Websocket Helper
 */
function techno_chatbot_websocket() {
    return Techno_Chatbot_Websocket::instance();
}

function techno_wss_check(){
    return techno_chatbot_websocket()->is_running();
}
