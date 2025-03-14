# ALMOE ID OAuth

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple)
[![License](https://img.shields.io/badge/License-GPL%20v2-orange)](https://www.gnu.org/licenses/gpl-2.0.html)

Integrates WordPress with ALMOE ID OAuth server for secure Single Sign-On (SSO) authentication.

## Description

ALMOE ID OAuth allows your WordPress site to connect seamlessly with ALMOE ID authentication system, enabling Single Sign-On (SSO) functionality for your users. With this plugin, users can log in to your WordPress site using their ALMOE ID accounts, streamlining the login process and improving security.

## Key Features

- **Single Sign-On (SSO)**: Allow users to log in with their ALMOE ID credentials
- **Secure Authentication**: Uses OAuth 2.0 with PKCE for enhanced security
- **Automatic Registration**: New users can be automatically registered in WordPress when they authenticate with ALMOE ID
- **User Mapping**: Existing WordPress users can link their accounts with ALMOE ID
- **Detailed Logging**: Track authentication events and monitor login activity
- **Customizable Integration**: Configure OAuth endpoints, button appearance, and user registration settings
- **Modern Design**: Attractive and responsive login button with customization options
- **Shortcode Support**: Add login buttons anywhere on your site with the `[almoe_login_button]` shortcode

## Benefits

- **Improved User Experience**: Streamline the login process with a single set of credentials
- **Enhanced Security**: Leverage ALMOE ID's security features for your WordPress site
- **Reduced Administration**: Centralize user management through ALMOE ID
- **Detailed Insights**: Track user authentication through comprehensive logs

## Installation

1. Clone this repository or download the ZIP file
2. Upload the `almoe-id-oauth` folder to the `/wp-content/plugins/` directory of your WordPress installation
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Navigate to the 'ALMOE ID OAuth' menu in your admin dashboard to configure the plugin
5. Register your WordPress site as an OAuth client in your ALMOE ID server:
   - Set redirect URI to: `https://your-site.com/almoe-id-callback`
   - Request necessary scopes: `view-user`
6. Enter your Client ID and Client Secret in the plugin settings
7. Configure additional settings as needed and test the connection

## Shortcode Usage

### Basic Usage
Add this shortcode anywhere (pages, posts, sidebar widgets):

```
[almoe_login_button]
```

### Example with All Parameters

```
[almoe_login_button text="Login with ALMOE ID" size="large" fullwidth="yes" redirect="https://yoursite.com/dashboard" class="my-custom-button"]
```

### Available Parameters

| Parameter | Description | Default | Options |
|-----------|-------------|---------|---------|
| `text` | Button text | "Login with ALMOE ID" | Any text |
| `size` | Button size | "normal" | "small", "normal", "large" |
| `fullwidth` | Full width button | "no" | "yes", "no" |
| `redirect` | Redirect URL after login | Current page | Any valid URL |
| `class` | Additional CSS classes | - | Any valid class name |

### Adding to Theme Templates

Add this code to your theme template files:

```php
<?php echo do_shortcode('[almoe_login_button]'); ?>
```

## Frequently Asked Questions

### What is ALMOE ID?
ALMOE ID is a secure authentication service that provides Single Sign-On (SSO) capabilities for your ecosystem of applications. It allows users to access multiple services with one set of credentials.

### How does the plugin handle new users?
When new users authenticate with ALMOE ID, they can be automatically registered in WordPress based on your configuration settings. You can control this behavior and set the default role for new users in the plugin settings.

### Can existing WordPress users connect their accounts to ALMOE ID?
Yes, existing WordPress users can link their accounts with ALMOE ID by logging in with their ALMOE ID credentials. The plugin will automatically match accounts based on email address.

### Is this plugin compatible with multisite installations?
Yes, the plugin works with WordPress multisite installations. Each site in the network can have its own configuration.

### What happens if ALMOE ID is unavailable?
Users will still be able to log in using the standard WordPress login form, ensuring your site remains accessible even if the ALMOE ID server is temporarily unavailable.

### How does the plugin handle security?
The plugin uses OAuth 2.0 with PKCE (Proof Key for Code Exchange) for enhanced security, preventing authorization code interception attacks. It also implements strict validation of tokens and state parameters to prevent CSRF attacks.

## Changelog

### 1.0.0
- Initial release

## Links

- [ALMOE ID Website](https://masjidalmubarokah.com/)
- [Support](https://masjidalmubarokah.com/support/)

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.
