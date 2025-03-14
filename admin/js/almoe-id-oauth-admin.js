/**
 * ALMOE ID OAuth Admin JavaScript
 * 
 * Handles interactive functionality in the admin dashboard
 */

(function($) {
    'use strict';

    // Initialize admin functionality when document is ready
    $(document).ready(function() {
        // Initialize test connection functionality
        initTestConnection();
        
        // Initialize auto-configure functionality
        initAutoConfigure();
        
        // Initialize button preview functionality
        initButtonPreview();
        
        // Initialize log filtering functionality
        initLogFiltering();
        
        // Initialize tooltips
        initTooltips();
    });

    /**
     * Initialize test connection functionality
     */
    function initTestConnection() {
        $('#almoe-id-oauth-test-connection').on('click', function() {
            const $button = $(this);
            const $statusDiv = $('#almoe-id-oauth-connection-status');
            
            // Disable button and show loading state
            $button.prop('disabled', true).text('Testing...');
            $statusDiv.removeClass('success error').empty();
            
            // Get client credentials from form fields
            const clientId = $('#almoe_id_oauth_client_id').val();
            const clientSecret = $('#almoe_id_oauth_client_secret').val();
            
            // Validate input
            if (!clientId || !clientSecret) {
                $statusDiv.addClass('error').text('Please enter Client ID and Client Secret before testing.');
                $button.prop('disabled', false).text('Test Connection');
                return;
            }
            
            // Make AJAX request to test connection
            $.ajax({
                url: almoeIdOauthAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'almoe_id_oauth_test_connection',
                    nonce: almoeIdOauthAdmin.nonce,
                    client_id: clientId,
                    client_secret: clientSecret
                },
                success: function(response) {
                    if (response.success) {
                        $statusDiv.addClass('success').text(response.data.message);
                    } else {
                        $statusDiv.addClass('error').text(response.data.message);
                    }
                },
                error: function() {
                    $statusDiv.addClass('error').text('Connection test failed due to a network error. Please try again.');
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text('Test Connection');
                }
            });
        });
    }

    /**
     * Initialize auto-configure functionality
     */
    function initAutoConfigure() {
        $('#almoe-id-oauth-auto-configure').on('click', function() {
            const $button = $(this);
            const $statusDiv = $('#almoe-id-oauth-connection-status');
            
            // Disable button and show loading state
            $button.prop('disabled', true).text('Configuring...');
            $statusDiv.removeClass('success error').empty();
            
            // Get discovery URL
            const discoveryUrl = $('#almoe_id_oauth_discovery_url').val();
            
            // Validate input
            if (!discoveryUrl) {
                $statusDiv.addClass('error').text('Please enter a Discovery URL before auto-configuring.');
                $button.prop('disabled', false).text('Auto-Configure Endpoints');
                return;
            }
            
            // Make AJAX request to auto-configure
            $.ajax({
                url: almoeIdOauthAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'almoe_id_oauth_auto_configure',
                    nonce: almoeIdOauthAdmin.nonce,
                    discovery_url: discoveryUrl
                },
                success: function(response) {
                    if (response.success) {
                        $statusDiv.addClass('success').text(response.data.message);
                        
                        // Update endpoint fields with discovered values
                        $('#almoe_id_oauth_auth_endpoint').val(response.data.auth_endpoint);
                        $('#almoe_id_oauth_token_endpoint').val(response.data.token_endpoint);
                        $('#almoe_id_oauth_userinfo_endpoint').val(response.data.userinfo_endpoint);
                    } else {
                        $statusDiv.addClass('error').text(response.data.message);
                    }
                },
                error: function() {
                    $statusDiv.addClass('error').text('Auto-configuration failed due to a network error. Please try again.');
                },
                complete: function() {
                    // Re-enable button
                    $button.prop('disabled', false).text('Auto-Configure Endpoints');
                }
            });
        });
    }

    /**
     * Initialize button preview functionality
     */
    function initButtonPreview() {
        // Update button preview text when input changes
        $('#almoe_id_oauth_button_text').on('input', function() {
            const buttonText = $(this).val() || 'Login with ALMOE ID';
            $('#almoe-id-oauth-button-preview').text(buttonText);
        });
    }

    /**
     * Initialize log filtering functionality
     */
    function initLogFiltering() {
        // Handle log filter form submission
        $('#almoe-id-oauth-log-filter-form').on('submit', function(e) {
            // Form will submit normally, no need to prevent default
            
            // Show loading state
            $('.almoe-id-oauth-logs-loading').show();
            $('.almoe-id-oauth-logs-table').addClass('loading');
        });
        
        // Handle date range picker (if using one)
        if ($.fn.daterangepicker) {
            $('.almoe-id-oauth-date-range').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                alwaysShowCalendars: true,
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });
            
            $('.almoe-id-oauth-date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                
                // Set hidden input values
                $('#almoe_id_oauth_date_from').val(picker.startDate.format('YYYY-MM-DD'));
                $('#almoe_id_oauth_date_to').val(picker.endDate.format('YYYY-MM-DD'));
            });
            
            $('.almoe-id-oauth-date-range').on('cancel.daterangepicker', function() {
                $(this).val('');
                
                // Clear hidden input values
                $('#almoe_id_oauth_date_from').val('');
                $('#almoe_id_oauth_date_to').val('');
            });
        }
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        // Add tooltip functionality for help icons
        $('.almoe-id-oauth-help-tip').hover(
            function() {
                const tooltipText = $(this).data('tip');
                const $tooltip = $('<div class="almoe-id-oauth-tooltip"></div>').text(tooltipText);
                
                // Position tooltip
                const position = $(this).position();
                $tooltip.css({
                    top: position.top + 20,
                    left: position.left - 100
                });
                
                // Add tooltip to document
                $('body').append($tooltip);
            },
            function() {
                // Remove tooltip when mouse leaves
                $('.almoe-id-oauth-tooltip').remove();
            }
        );
    }

    /**
     * User mapping management
     */
    if ($('#almoe-id-oauth-user-mapping').length) {
        // Initialize user search
        $('#almoe-id-oauth-user-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            // Filter user rows based on search term
            $('.almoe-id-oauth-user-table tbody tr').each(function() {
                const userName = $(this).find('td:nth-child(2)').text().toLowerCase();
                const userEmail = $(this).find('td:nth-child(3)').text().toLowerCase();
                
                if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Handle user mapping actions
        $('.almoe-id-oauth-unlink-user').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to unlink this user from ALMOE ID? The user will still exist in WordPress but will need to log in with their WordPress credentials.')) {
                const userId = $(this).data('user-id');
                const $row = $(this).closest('tr');
                
                // Make AJAX request to unlink user
                $.ajax({
                    url: almoeIdOauthAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'almoe_id_oauth_unlink_user',
                        nonce: almoeIdOauthAdmin.nonce,
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update row to show unlinked status
                            $row.find('td:nth-child(4)').text('Not linked');
                            $row.find('.almoe-id-oauth-unlink-user').remove();
                        } else {
                            alert('Failed to unlink user: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Failed to unlink user due to a network error. Please try again.');
                    }
                });
            }
        });
    }

})(jQuery);