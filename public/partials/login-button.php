<?php
/**
 * Template for ALMOE ID login button
 *
 * @package ALMOE_ID_OAuth
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get button text from settings
$button_text = get_option('almoe_id_oauth_button_text', __('Login with ALMOE ID', 'almoe-id-oauth'));

// Logo path
$logo_url = ALMOE_ID_OAUTH_URL . 'assets/images/almoelogo.svg';
?>

<style>
/* Login Button Styling */
.almoe-id-login-container {
    margin: 24px 0;
    text-align: center;
    width: 100%;
}

.almoe-id-login-separator {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 20px 0;
    color: #6c757d;
    font-size: 14px;
}

.almoe-id-login-separator::before,
.almoe-id-login-separator::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
}

.almoe-id-login-separator span {
    margin: 0 10px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
}

.almoe-id-login-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 320px;
    margin: 0 auto;
    padding: 12px 16px;
    border-radius: 4px;
    background: linear-gradient(135deg, #0E5C67 0%, #0e6730 100%);
    color: white !important;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    position: relative;
}

.almoe-id-login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    color: white !important;
    text-decoration: none;
}

.almoe-id-icon {
    display: inline-flex;
    margin-right: 10px;
    align-items: center;
    justify-content: center;
}

.almoe-id-icon img {
    width: 24px;
    height: 24px;
    object-fit: contain;
    vertical-align: middle;
}

.almoe-id-button-text {
    font-size: 16px;
    letter-spacing: 0.5px;
}

/* Responsive styles */
@media (max-width: 480px) {
    .almoe-id-login-button {
        max-width: 100%;
    }
}
</style>

<div class="almoe-id-login-container">
    <div class="almoe-id-login-separator">
        <span><?php esc_html_e('OR', 'almoe-id-oauth'); ?></span>
    </div>
    
    <a href="<?php echo esc_url($auth_url); ?>" class="almoe-id-login-button">
        <span class="almoe-id-icon">
            <img src="<?php echo esc_url($logo_url); ?>" alt="ALMOE ID" width="24" height="24">
        </span>
        <span class="almoe-id-button-text"><?php echo esc_html($button_text); ?></span>
    </a>
</div>