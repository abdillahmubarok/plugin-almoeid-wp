/**
 * ALMOE ID OAuth Frontend JavaScript
 * 
 * Handles login button interactions and OAuth flow support
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initLoginButton();
        handleOAuthCallback();
    });

    /**
     * Initialize login button behavior
     */
    function initLoginButton() {
        $('.almoe-id-login-button').on('click', function(e) {
            // The button is already an <a> tag, so no need to preventDefault
            // This is just for any additional functionality we might want to add
            
            // Add loading state to button
            $(this).addClass('loading');
            
            // We could add analytics tracking here if needed
            if (typeof gtag === 'function') {
                gtag('event', 'click', {
                    'event_category': 'ALMOE ID OAuth',
                    'event_label': 'Login Button'
                });
            }
        });
    }

    /**
     * Handle OAuth callback
     * Process URL parameters when returning from ALMOE ID server
     */
    function handleOAuthCallback() {
        // Check if we're on the callback page
        if (window.location.href.indexOf('almoe-id-callback') > -1) {
            // Show loading indicator
            $('body').append('<div class="almoe-id-oauth-loader">Completing authentication...</div>');
            
            // Parse URL params
            const urlParams = new URLSearchParams(window.location.search);
            
            // Check for errors
            if (urlParams.has('error')) {
                const error = urlParams.get('error');
                const errorDescription = urlParams.get('error_description') || 'Unknown error';
                
                console.error('ALMOE ID OAuth Error:', error, errorDescription);
                
                // Display error message
                $('body').append(
                    '<div class="almoe-id-oauth-error">' +
                    '<h3>Authentication Error</h3>' +
                    '<p>' + errorDescription + '</p>' +
                    '<p><a href="' + almoeIdOauth.loginUrl + '">Return to login</a></p>' +
                    '</div>'
                );
                
                // Hide loader
                $('.almoe-id-oauth-loader').hide();
            }
            
            // The rest of the callback processing is handled server-side
            // This is just for providing visual feedback to the user
        }
    }

    /**
     * Store state in session storage
     * This is a backup for browsers that don't support cookies well
     */
    function storeState(state) {
        try {
            sessionStorage.setItem('almoe_id_oauth_state', state);
            return true;
        } catch (e) {
            console.warn('SessionStorage not available, falling back to cookies only');
            return false;
        }
    }

    /**
     * Get state from session storage
     */
    function getState() {
        try {
            return sessionStorage.getItem('almoe_id_oauth_state');
        } catch (e) {
            return null;
        }
    }

    /**
     * Clear state from session storage
     */
    function clearState() {
        try {
            sessionStorage.removeItem('almoe_id_oauth_state');
        } catch (e) {
            // Ignore errors
        }
    }

    /**
     * Handle errors during the OAuth process
     */
    function handleOAuthError(error, errorDescription) {
        console.error('ALMOE ID OAuth Error:', error, errorDescription);
        
        // Create an error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'almoe-id-oauth-error';
        errorElement.innerHTML = '<h3>Authentication Error</h3><p>' + errorDescription + '</p>';
        
        // Add a retry button
        const retryButton = document.createElement('button');
        retryButton.className = 'almoe-id-oauth-retry-button';
        retryButton.innerText = 'Try Again';
        retryButton.onclick = function() {
            window.location.href = almoeIdOauth.loginUrl;
        };
        
        errorElement.appendChild(retryButton);
        
        // Add error element to the page
        document.body.appendChild(errorElement);
    }

})(jQuery);