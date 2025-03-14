<?php
/**
 * Admin user mapping page template
 *
 * @package ALMOE_ID_OAuth
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get users with ALMOE ID mapping
$users_with_mapping = get_users(array(
    'meta_key' => 'almoe_id_oauth_id',
    'meta_compare' => 'EXISTS',
));

// Get total users count for pagination
$total_users = count_users();
$total_mapped_users = count($users_with_mapping);
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
        <div class="almoe-id-oauth-admin-main" id="almoe-id-oauth-user-mapping">
            <div class="almoe-id-oauth-user-summary">
                <div class="almoe-id-oauth-summary-box">
                    <h3><?php esc_html_e('User Statistics', 'almoe-id-oauth'); ?></h3>
                    <div class="almoe-id-oauth-summary-content">
                        <div class="almoe-id-oauth-stat">
                            <span class="almoe-id-oauth-stat-value"><?php echo esc_html($total_users['total_users']); ?></span>
                            <span class="almoe-id-oauth-stat-label"><?php esc_html_e('Total WordPress Users', 'almoe-id-oauth'); ?></span>
                        </div>
                        <div class="almoe-id-oauth-stat">
                            <span class="almoe-id-oauth-stat-value"><?php echo esc_html($total_mapped_users); ?></span>
                            <span class="almoe-id-oauth-stat-label"><?php esc_html_e('Users Linked to ALMOE ID', 'almoe-id-oauth'); ?></span>
                        </div>
                        <div class="almoe-id-oauth-stat">
                            <span class="almoe-id-oauth-stat-value"><?php echo esc_html(round(($total_mapped_users / $total_users['total_users']) * 100, 1)); ?>%</span>
                            <span class="almoe-id-oauth-stat-label"><?php esc_html_e('Users Using ALMOE ID', 'almoe-id-oauth'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="almoe-id-oauth-user-tools">
                <div class="almoe-id-oauth-search-box">
                    <label for="almoe-id-oauth-user-search"><?php esc_html_e('Search Users:', 'almoe-id-oauth'); ?></label>
                    <input type="text" id="almoe-id-oauth-user-search" placeholder="<?php esc_attr_e('Search by name or email...', 'almoe-id-oauth'); ?>">
                </div>
            </div>
            
            <table class="almoe-id-oauth-user-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'almoe-id-oauth'); ?></th>
                        <th><?php esc_html_e('Name', 'almoe-id-oauth'); ?></th>
                        <th><?php esc_html_e('Email', 'almoe-id-oauth'); ?></th>
                        <th><?php esc_html_e('ALMOE ID Status', 'almoe-id-oauth'); ?></th>
                        <th><?php esc_html_e('Last Login', 'almoe-id-oauth'); ?></th>
                        <th><?php esc_html_e('Actions', 'almoe-id-oauth'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_with_mapping)) : ?>
                        <tr>
                            <td colspan="6"><?php esc_html_e('No users are currently linked with ALMOE ID.', 'almoe-id-oauth'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($users_with_mapping as $user) : 
                            $almoe_id = get_user_meta($user->ID, 'almoe_id_oauth_id', true);
                            $last_login = get_user_meta($user->ID, 'almoe_id_oauth_last_login', true);
                            
                            // Get the user's last login through ALMOE ID
                            global $wpdb;
                            $last_login_data = $wpdb->get_row(
                                $wpdb->prepare(
                                    "SELECT created_at FROM {$wpdb->prefix}almoe_id_oauth_logs 
                                    WHERE user_id = %d AND event_type = 'login_success' 
                                    ORDER BY created_at DESC LIMIT 1",
                                    $user->ID
                                )
                            );
                            
                            $last_login_time = $last_login_data ? $last_login_data->created_at : '';
                        ?>
                            <tr>
                                <td><?php echo esc_html($user->ID); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
                                        <?php echo esc_html($user->display_name); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><?php echo esc_html($almoe_id ? __('Linked', 'almoe-id-oauth') : __('Not linked', 'almoe-id-oauth')); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($last_login_time)) {
                                        $time_diff = human_time_diff(strtotime($last_login_time), current_time('timestamp'));
                                        echo esc_html(sprintf(__('%s ago', 'almoe-id-oauth'), $time_diff));
                                    } else {
                                        echo esc_html__('Never', 'almoe-id-oauth');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($almoe_id) : ?>
                                        <button type="button" class="button button-small almoe-id-oauth-unlink-user" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                            <?php esc_html_e('Unlink', 'almoe-id-oauth'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="almoe-id-oauth-admin-sidebar">
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('About User Mapping', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('This page shows WordPress users who have linked their accounts with ALMOE ID.', 'almoe-id-oauth'); ?></p>
                    <p><?php esc_html_e('Users can be linked to ALMOE ID in two ways:', 'almoe-id-oauth'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Existing WordPress users can log in with ALMOE ID', 'almoe-id-oauth'); ?></li>
                        <li><?php esc_html_e('New users can register through ALMOE ID authentication', 'almoe-id-oauth'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('Actions', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('You can perform the following actions:', 'almoe-id-oauth'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Unlink: Disconnects a user from their ALMOE ID account. They can still log in with their WordPress credentials.', 'almoe-id-oauth'); ?></li>
                    </ul>
                    <p><strong><?php esc_html_e('Note:', 'almoe-id-oauth'); ?></strong> <?php esc_html_e('Unlinking a user does not delete their WordPress account or ALMOE ID account.', 'almoe-id-oauth'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>