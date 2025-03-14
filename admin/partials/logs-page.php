<?php
/**
 * Admin logs page template
 *
 * @package ALMOE_ID_OAuth
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Process filter parameters
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';
$event_type = isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// Get logs based on filters
$logs_args = array(
    'number' => $per_page,
    'offset' => ($current_page - 1) * $per_page,
    'user_id' => $user_id,
    'event_type' => $event_type,
    'date_from' => $date_from,
    'date_to' => $date_to,
);

$logs = \ALMOE_ID_OAuth\Utilities::get_logs($logs_args);
$total_logs = \ALMOE_ID_OAuth\Utilities::count_logs($logs_args);
$total_pages = ceil($total_logs / $per_page);

// Prepare pagination
$pagination_args = array(
    'base' => add_query_arg('paged', '%#%'),
    'format' => '',
    'current' => $current_page,
    'total' => $total_pages,
);

// Add any filtering args to pagination
if (!empty($user_id)) {
    $pagination_args['add_args']['user_id'] = $user_id;
}
if (!empty($event_type)) {
    $pagination_args['add_args']['event_type'] = $event_type;
}
if (!empty($date_from)) {
    $pagination_args['add_args']['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $pagination_args['add_args']['date_to'] = $date_to;
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
            <!-- Filters Form -->
            <form method="get" action="" id="almoe-id-oauth-log-filter-form">
                <input type="hidden" name="page" value="almoe-id-oauth-logs">
                
                <div class="almoe-id-oauth-logs-filter">
                    <div class="filter-item">
                        <label for="user_id"><?php esc_html_e('User ID:', 'almoe-id-oauth'); ?></label>
                        <input type="number" id="user_id" name="user_id" value="<?php echo esc_attr($user_id); ?>" min="0">
                    </div>
                    
                    <div class="filter-item">
                        <label for="event_type"><?php esc_html_e('Event Type:', 'almoe-id-oauth'); ?></label>
                        <select id="event_type" name="event_type">
                            <option value=""><?php esc_html_e('All Events', 'almoe-id-oauth'); ?></option>
                            <option value="login_success" <?php selected($event_type, 'login_success'); ?>><?php esc_html_e('Login Success', 'almoe-id-oauth'); ?></option>
                            <option value="login_failure" <?php selected($event_type, 'login_failure'); ?>><?php esc_html_e('Login Failure', 'almoe-id-oauth'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item date-range">
                        <label><?php esc_html_e('Date Range:', 'almoe-id-oauth'); ?></label>
                        <div class="date-inputs">
                            <input type="text" class="almoe-id-oauth-date-range" placeholder="<?php esc_attr_e('Select dates...', 'almoe-id-oauth'); ?>" value="<?php echo (!empty($date_from) && !empty($date_to)) ? esc_attr($date_from . ' - ' . $date_to) : ''; ?>">
                            <input type="hidden" id="almoe_id_oauth_date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                            <input type="hidden" id="almoe_id_oauth_date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                        </div>
                    </div>
                    
                    <div class="filter-item filter-actions">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'almoe-id-oauth'); ?></button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=almoe-id-oauth-logs')); ?>" class="button button-secondary"><?php esc_html_e('Reset', 'almoe-id-oauth'); ?></a>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Logs Table -->
            <div class="almoe-id-oauth-logs-container">
                <?php if (empty($logs)) : ?>
                    <div class="almoe-id-oauth-no-logs">
                        <p><?php esc_html_e('No logs found matching your criteria.', 'almoe-id-oauth'); ?></p>
                    </div>
                <?php else : ?>
                    <p class="almoe-id-oauth-logs-count">
                        <?php printf(
                            esc_html(_n('Showing %1$d of %2$d log entry', 'Showing %1$d of %2$d log entries', $total_logs, 'almoe-id-oauth')),
                            count($logs),
                            $total_logs
                        ); ?>
                    </p>
                    
                    <table class="almoe-id-oauth-logs-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'almoe-id-oauth'); ?></th>
                                <th><?php esc_html_e('Time', 'almoe-id-oauth'); ?></th>
                                <th><?php esc_html_e('User', 'almoe-id-oauth'); ?></th>
                                <th><?php esc_html_e('Event', 'almoe-id-oauth'); ?></th>
                                <th><?php esc_html_e('Details', 'almoe-id-oauth'); ?></th>
                                <th><?php esc_html_e('IP Address', 'almoe-id-oauth'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : 
                                $user_data = '';
                                if ($log['user_id'] > 0) {
                                    $user = get_userdata($log['user_id']);
                                    if ($user) {
                                        $user_data = sprintf(
                                            '<a href="%s">%s</a>',
                                            esc_url(get_edit_user_link($log['user_id'])),
                                            esc_html($user->display_name)
                                        );
                                    } else {
                                        $user_data = sprintf(__('User ID: %d (deleted)', 'almoe-id-oauth'), $log['user_id']);
                                    }
                                } else {
                                    $user_data = __('No user', 'almoe-id-oauth');
                                }
                                
                                // Get event details
                                $event_data = $log['event_data'];
                                $details = '';
                                
                                if ($log['event_type'] === 'login_success') {
                                    $almoe_id = isset($event_data['almoe_id']) ? $event_data['almoe_id'] : '';
                                    $email = isset($event_data['email']) ? $event_data['email'] : '';
                                    
                                    $details = sprintf(
                                        __('ALMOE ID: %s, Email: %s', 'almoe-id-oauth'),
                                        esc_html($almoe_id),
                                        esc_html($email)
                                    );
                                } elseif ($log['event_type'] === 'login_failure') {
                                    $error = isset($event_data['error']) ? $event_data['error'] : '';
                                    $details = sprintf(__('Error: %s', 'almoe-id-oauth'), esc_html($error));
                                }
                                
                                // Get IP address
                                $ip = '';
                                if (isset($event_data['ip'])) {
                                    $ip = $event_data['ip'];
                                }
                                
                                // Get event class for styling
                                $event_class = '';
                                if ($log['event_type'] === 'login_success') {
                                    $event_class = 'event-success';
                                } elseif ($log['event_type'] === 'login_failure') {
                                    $event_class = 'event-failure';
                                }
                            ?>
                                <tr>
                                    <td><?php echo esc_html($log['id']); ?></td>
                                    <td>
                                        <?php 
                                        $time_diff = human_time_diff(strtotime($log['created_at']), current_time('timestamp'));
                                        echo esc_html(sprintf(__('%s ago', 'almoe-id-oauth'), $time_diff));
                                        echo '<br><span class="log-date">' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']))) . '</span>';
                                        ?>
                                    </td>
                                    <td><?php echo $user_data; ?></td>
                                    <td class="<?php echo esc_attr($event_class); ?>">
                                        <?php 
                                        if ($log['event_type'] === 'login_success') {
                                            esc_html_e('Login Success', 'almoe-id-oauth');
                                        } elseif ($log['event_type'] === 'login_failure') {
                                            esc_html_e('Login Failure', 'almoe-id-oauth');
                                        } else {
                                            echo esc_html($log['event_type']);
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $details; ?></td>
                                    <td><?php echo esc_html($ip); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1) : ?>
                        <div class="almoe-id-oauth-pagination">
                            <?php echo paginate_links($pagination_args); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="almoe-id-oauth-admin-sidebar">
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('About Logs', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><?php esc_html_e('This page shows login activity logs for ALMOE ID authentication.', 'almoe-id-oauth'); ?></p>
                    <p><?php esc_html_e('You can filter logs by user, event type, and date range.', 'almoe-id-oauth'); ?></p>
                    <p><?php esc_html_e('Logs are automatically cleaned after 30 days to keep the database optimized.', 'almoe-id-oauth'); ?></p>
                </div>
            </div>
            
            <div class="almoe-id-oauth-box">
                <h3><?php esc_html_e('Log Legend', 'almoe-id-oauth'); ?></h3>
                <div class="almoe-id-oauth-box-content">
                    <p><span class="event-success">●</span> <?php esc_html_e('Login Success: User successfully authenticated with ALMOE ID', 'almoe-id-oauth'); ?></p>
                    <p><span class="event-failure">●</span> <?php esc_html_e('Login Failure: Authentication attempt failed', 'almoe-id-oauth'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>