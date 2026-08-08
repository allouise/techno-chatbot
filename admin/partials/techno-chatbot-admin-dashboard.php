<?php
/**
 * Admin Settings view: Dashboard Page
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.1.4
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin/partials
 */
?>

<div class="wrap">
    <div id="techno-chatbot-dashboard" class="techno-dashboard-container">
    
        <!-- Dashboard Header -->
        <header class="techno-dashboard-header">
            <div class="techno-header-titles">
                <h1 class="techno-dashboard-title"><?php esc_html_e( 'Welcome to Techno Chatbot', 'techno-chatbot' ); ?></h1>
                <p class="techno-dashboard-subtitle">Overview of your account plan, AI usage, and dynamic content status.</p>
            </div>
            <div class="techno-header-status-badge">
                <span class="techno-status-dot <?php echo ($chatbot_status == 'Active')? 'active' : '' ?>"></span>
                <span class="techno-status-label">Chatbot <?php echo ($chatbot_status == 'Active')? 'Active' : 'Disabled' ?></span>
            </div>
        </header>

        <!-- Overview Grid -->
        <div class="techno-dashboard-grid">
            
            <!-- Card 1: Subscription Plan Details -->
            <div class="techno-card techno-card-plan">
                <div class="techno-card-header">
                    <h2 class="techno-card-title">Current Plan</h2>
                    <span class="techno-badge <?php echo ($license_data['status'] != 'active')? 'inactive' : '' ?>"><?php echo ucwords($license_data['status']); ?></span>
                </div>
                <div class="techno-card-body techno-stats-inline">
                    <div class="techno-metric-group">
                        <span class="techno-metric-label">Plan Name</span>
                        <span class="techno-metric-value" id="techno-plan-name"><?php echo ucwords($license_data['plan']); ?></span>
                    </div>
                    <div class="techno-metric-group">
                        <span class="techno-metric-label">Last Check</span>
                        <span class="techno-metric-value-sub" id="techno-last-check"><?php echo !empty($license_data['last_check']) ? date('Y-m-d H:i:s', $license_data['last_check']) : ''; ?></span>
                    </div>
                </div>
            </div>

            <!-- Card 2: AI Assistance & Usage / Multi-language limits -->
            <div class="techno-card techno-card-ai">
                <div class="techno-card-header">
                    <h2 class="techno-card-title">AI Assistance</h2>
                    <span class="techno-status-pill <?php echo ($aiallowed == 1)? 'active' : ''; ?>" id="techno-ai-status"><?php echo ($aiallowed == 1)? 'Active' : 'Inactive'; ?></span>
                </div>
                <div class="techno-card-body">
                    <div class="techno-usage-details">
                        <div class="techno-usage-text">
                            <span class="techno-metric-label">Remaining Allowance</span>
                            <span class="techno-usage-numbers"><strong><?php echo $remaining_limit; ?></strong> / <span id="techno-ai-limit"><?php echo $ai_assistance_limit; ?> requests</span></span>
                        </div>
                        <!-- Usage Bar -->
                        <div class="techno-progress-bar-bg">
                            <div class="techno-progress-bar-fill <?php echo $remaining_percentage >= 90 ? 'danger' : ( $remaining_percentage >= 60 ? 'warning' : '' ); ?>" style="width: <?php echo esc_attr( $remaining_percentage ); ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Content Sources Stats -->
            <div class="techno-card techno-card-stats">
                <div class="techno-card-header">
                    <h2 class="techno-card-title">Knowledge Base</h2>
                    <span class="techno-status-pill <?php echo ($aiallowed == 1)? 'ai' : 'faq'; ?>"><?php echo ($aiallowed == 1)? 'Using AI' : 'Using FAQ'; ?></span>
                </div>
                <div class="techno-card-body techno-stats-inline">
                    <div class="techno-stat-item">
                        <a class="techno-stat-number" href="<?php echo add_query_arg([ 'post_type' => 'techno_chatbot_faq' ], admin_url('edit.php')); ?>" id="techno-faq-count"><?php echo $faq_count; ?></a>
                        <span class="techno-stat-label">FAQ Count</span>
                    </div>
                    <div class="techno-stat-divider"></div>
                    <div class="techno-stat-item">
                        <a class="techno-stat-number" href="<?php echo add_query_arg([ 'post_type' => 'techno_chatbot_aidb' ], admin_url('edit.php')); ?>" id="techno-ai-source-count"><?php echo $crawled_count; ?></a>
                        <span class="techno-stat-label">AI Source Count</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Multi-language -->
            <div class="techno-card techno-card-multilang">
                <div class="techno-card-header">
                    <h2 class="techno-card-title">Multi Language</h2>
                    <span class="techno-status-pill <?php echo ($language_count >= 0)? 'active' : ''; ?>" id="techno-ai-status"><?php echo ($language_limit >= 0)? 'Active' : 'Inactive'; ?></span>
                </div>
                <div class="techno-card-body">
                    <div class="techno-usage-details">
                        <div class="techno-usage-text">
                            <span class="techno-metric-label">Language Limit</span>
                            <span class="techno-usage-numbers"><strong><?php echo $active_languages; ?></strong> / <span id="techno-ai-limit"><?php echo $language_limit; ?> languages</span></span>
                        </div>
                        <!-- Usage Bar -->
                        <div class="techno-progress-bar-bg">
                            <div class="techno-progress-bar-fill <?php echo $langused_percentage >= 90 ? 'danger' : ( $langused_percentage >= 60 ? 'warning' : '' ); ?>" style="width: <?php echo esc_attr( $langused_percentage ); ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Dashboard Footer & Support -->
        <footer class="techno-dashboard-footer">
            <p class="techno-support-text">
                Need help or have questions about your account? Contact support at 
                <a href="mailto:<?php echo TECHNO_CHATBOT_SUPPORT_EMAIL; ?>" class="techno-support-link"><?php echo TECHNO_CHATBOT_SUPPORT_EMAIL; ?></a>.
            </p>
        </footer>

    </div>
</div>