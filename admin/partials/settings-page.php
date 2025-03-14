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
            <?php esc_html_e('ALMOE ID', 'almoe-id-oauth'); ?>
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
    <h3><?php esc_html_e('Instructions for using Shortcode', 'almoe-id-oauth'); ?></h3>
    <div class="almoe-id-oauth-box-content">
        <p><?php esc_html_e('The ALMOE ID OAuth plugin provides a shortcode that you can use to display the ALMOE ID login button anywhere on your WordPress site.', 'almoe-id-oauth'); ?></p>
        
        <h4><?php esc_html_e('Basic Usage', 'almoe-id-oauth'); ?></h4>
        <p><code>[almoe_login_button]</code></p>
        
        <h4><?php esc_html_e('Available Parameters', 'almoe-id-oauth'); ?></h4>
        <ul>
            <li><strong>text</strong>: <?php esc_html_e('Custom button text (default: "Login with ALMOE ID")', 'almoe-id-oauth'); ?></li>
            <li><strong>redirect</strong>: <?php esc_html_e('URL to redirect after login (default: current page)', 'almoe-id-oauth'); ?></li>
            <li><strong>class</strong>: <?php esc_html_e('Additional CSS classes for the button', 'almoe-id-oauth'); ?></li>
            <li><strong>size</strong>: <?php esc_html_e('Button size - "small", "normal", or "large" (default: "normal")', 'almoe-id-oauth'); ?></li>
            <li><strong>fullwidth</strong>: <?php esc_html_e('Set to "yes" for full-width button (default: "no")', 'almoe-id-oauth'); ?></li>
        </ul>
        
        <h4><?php esc_html_e('Examples', 'almoe-id-oauth'); ?></h4>
        <p><strong><?php esc_html_e('Custom text:', 'almoe-id-oauth'); ?></strong><br>
        <code>[almoe_login_button text="Masuk dengan ALMOE ID"]</code></p>
        
        <p><strong><?php esc_html_e('Large button:', 'almoe-id-oauth'); ?></strong><br>
        <code>[almoe_login_button size="large"]</code></p>
        
        <p><strong><?php esc_html_e('Custom redirect:', 'almoe-id-oauth'); ?></strong><br>
        <code>[almoe_login_button redirect="https://example.com/dashboard"]</code></p>
        
        <p><strong><?php esc_html_e('Multiple parameters:', 'almoe-id-oauth'); ?></strong><br>
        <code>[almoe_login_button text="Masuk Sekarang" size="large" fullwidth="yes" class="my-custom-button"]</code></p>
        
        <h4><?php esc_html_e('Using in Templates', 'almoe-id-oauth'); ?></h4>
        <p><?php esc_html_e('You can also add the login button directly in theme or plugin templates using the do_shortcode() function:', 'almoe-id-oauth'); ?></p>
        <p><code>&lt;?php echo do_shortcode('[almoe_login_button]'); ?&gt;</code></p>
    </div>
</div>
            
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('Need Help?', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('For questions, issues, or feature requests, please contact ALMOE ID support:', 'almoe-id-oauth'); ?></p>
                    <ul>
                        <li><a href="https://id.masjidalmubarokah.com/support" target="_blank"><?php esc_html_e('Support Website', 'almoe-id-oauth'); ?></a></li>
                        <li><a href="mailto:dkm@masjidalmubarokah.com"><?php esc_html_e('Email Support', 'almoe-id-oauth'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>