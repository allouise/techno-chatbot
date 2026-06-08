<?php
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'plugin-update-checker/plugin-update-checker.php';
$updateChecker = PucFactory::buildUpdateChecker( 'https://github.com/allouise/techno-chatbot/', plugin_dir_path( dirname( __FILE__ ) ) . 'techno-chatbot.php', 'techno-chatbot' );

// Use GitHub Releases
$updateChecker->getVcsApi()->enableReleaseAssets();