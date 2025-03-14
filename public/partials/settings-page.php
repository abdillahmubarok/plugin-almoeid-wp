<?php
/**
 * Admin settings page template
 *
 * @package ALMOE_ID_OAuth
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap almoe-id-oauth-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="almoe-id-oauth-admin-header">
        <div class="almoe-id-oauth-logo">
            <img src="<?php echo esc_url(ALMOE_ID_OAUTH_URL . 'admin/images/almoelogo.svg'); ?>" alt="ALMOE ID">
        </div>
        <div class="almoe-id-oauth-version">
            <?php printf(esc_html__('Version %s', 'almoe-id-oauth'), ALMOE_ID_OAUTH_VERSION); ?>
        </div>
    </div>
    
    <div class="almoe-id-oauth-admin-content">
        <div class="almoe-id-oauth-admin-main">
            <?php settings_errors('almoe_id_oauth_messages'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('almoe_id_oauth_settings');
                do_settings_sections('almoe_id_oauth_settings');
                submit_button();
                ?>
            </form>
        </div>
        
        <div class="almoe-id-oauth-admin-sidebar">
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('Configuration Instructions', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('To set up ALMOE ID OAuth integration:', 'almoe-id-oauth'); ?></p>
                    <ol>
                        <li><?php esc_html_e('Register this site as a client in your ALMOE ID server', 'almoe-id-oauth'); ?></li>
                        <li><?php esc_html_e('Set the redirect URI to:', 'almoe-id-oauth'); ?> <code><?php echo esc_html(site_url('almoe-id-callback')); ?></code></li>
                        <li><?php esc_html_e('Enter your Client ID and Client Secret in the settings', 'almoe-id-oauth'); ?></li>
                        <li><?php esc_html_e('Configure the OAuth endpoints or use auto-discovery', 'almoe-id-oauth'); ?></li>
                        <li><?php esc_html_e('Test the connection to ensure everything works', 'almoe-id-oauth'); ?></li>
                    </ol>
                </div>
            </div>
            
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('Need Help?', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('For questions, issues, or feature requests, please contact ALMOE ID support:', 'almoe-id-oauth'); ?></p>
                    <ul>
                        <li><a href="https://masjidalmubarokah.com/support" target="_blank"><?php esc_html_e('Support Website', 'almoe-id-oauth'); ?></a></li>
                        <li><a href="mailto:support@masjidalmubarokah.com"><?php esc_html_e('Email Support', 'almoe-id-oauth'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>