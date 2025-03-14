=== ALMOE ID OAuth ===
Contributors: almoeid
Tags: oauth, sso, login, authentication, almoe-id
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrates WordPress with ALMOE ID OAuth server for secure Single Sign-On (SSO) authentication.

== Description ==

ALMOE ID OAuth allows your WordPress site to connect seamlessly with ALMOE ID authentication system, enabling Single Sign-On (SSO) functionality for your users. With this plugin, users can log in to your WordPress site using their ALMOE ID accounts, streamlining the login process and improving security.

### Key Features

* **Single Sign-On (SSO)**: Allow users to log in with their ALMOE ID credentials
* **Secure Authentication**: Uses OAuth 2.0 with PKCE for enhanced security
* **Automatic Registration**: New users can be automatically registered in WordPress when they authenticate with ALMOE ID
* **User Mapping**: Existing WordPress users can link their accounts with ALMOE ID
* **Detailed Logging**: Track authentication events and monitor login activity
* **Customizable Integration**: Configure OAuth endpoints, button appearance, and user registration settings
* **Modern Design**: Attractive and responsive login button with customization options

* **Single Sign-On (SSO)**: Allow users to log in with their ALMOE ID credentials
* **Secure Authentication**: Uses OAuth 2.0 with PKCE for enhanced security
* **Automatic Registration**: New users can be automatically registered in WordPress when they authenticate with ALMOE ID
* **User Mapping**: Existing WordPress users can link their accounts with ALMOE ID
* **Detailed Logging**: Track authentication events and monitor login activity
* **Customizable Integration**: Configure OAuth endpoints, button appearance, and user registration settings
* **Modern Design**: Attractive and responsive login button with customization options
* **Shortcode Support**: Add login buttons anywhere on your site with the `[almoe_login_button]` shortcode


### Benefits

* **Improved User Experience**: Streamline the login process with a single set of credentials
* **Enhanced Security**: Leverage ALMOE ID's security features for your WordPress site
* **Reduced Administration**: Centralize user management through ALMOE ID
* **Detailed Insights**: Track user authentication through comprehensive logs

== Installation ==

1. Upload the `almoe-id-oauth` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to the 'ALMOE ID OAuth' menu in your admin dashboard to configure the plugin.
4. Register your WordPress site as an OAuth client in your ALMOE ID server:
   * Set redirect URI to: `https://your-site.com/almoe-id-callback`
   * Request necessary scopes: `view-user`
5. Enter your Client ID and Client Secret in the plugin settings.
6. Configure additional settings as needed and test the connection.


## Cara Menggunakan Shortcode Login Button ALMOE ID

### Penggunaan Dasar
Cukup tambahkan kode berikut di mana saja (halaman, post, sidebar widget):

```
[almoe_login_button]
```

### Contoh dengan Parameter Lengkap:

```
[almoe_login_button text="Masuk dengan ALMOE ID" size="large" fullwidth="yes" redirect="https://situsanda.com/dashboard" class="my-custom-button"]
```

### Pengaturan Parameter:

1. `text` - Teks yang akan ditampilkan pada tombol
2. `size` - Ukuran tombol (`small`, `normal`, atau `large`)
3. `fullwidth` - Apakah tombol akan penuh lebar (`yes` atau `no`)
4. `redirect` - URL tujuan setelah berhasil login
5. `class` - Kelas CSS tambahan untuk styling kustom

### Memasang di Sidebar Widget:

1. Buka dashboard WordPress > Appearance > Widgets
2. Tambahkan widget "Text" atau "Custom HTML" ke sidebar
3. Masukkan shortcode `[almoe_login_button]` di widget tersebut
4. Simpan widget

### Memasang di Theme Template:

Tambahkan kode berikut di file template yang sesuai:

```php
<?php echo do_shortcode('[almoe_login_button]'); ?>
```

### Contoh Preview Hasil:

- Button Normal: Tombol login standar
- Button Small: Tombol login ukuran kecil, cocok untuk sidebar
- Button Large: Tombol login ukuran besar, cocok untuk Call to Action
- Button Full Width: Tombol yang memenuhi lebar container, cocok untuk mobile


== Frequently Asked Questions ==

= What is ALMOE ID? =

ALMOE ID is a secure authentication service that provides Single Sign-On (SSO) capabilities for your ecosystem of applications. It allows users to access multiple services with one set of credentials.

= How does the plugin handle new users? =

When new users authenticate with ALMOE ID, they can be automatically registered in WordPress based on your configuration settings. You can control this behavior and set the default role for new users in the plugin settings.

= Can existing WordPress users connect their accounts to ALMOE ID? =

Yes, existing WordPress users can link their accounts with ALMOE ID by logging in with their ALMOE ID credentials. The plugin will automatically match accounts based on email address.

= Is this plugin compatible with multisite installations? =

Yes, the plugin works with WordPress multisite installations. Each site in the network can have its own configuration.

= What happens if ALMOE ID is unavailable? =

Users will still be able to log in using the standard WordPress login form, ensuring your site remains accessible even if the ALMOE ID server is temporarily unavailable.

= How does the plugin handle security? =

The plugin uses OAuth 2.0 with PKCE (Proof Key for Code Exchange) for enhanced security, preventing authorization code interception attacks. It also implements strict validation of tokens and state parameters to prevent CSRF attacks.


== Screenshots ==

1. ALMOE ID OAuth login button on WordPress login form
2. Plugin settings page
3. User mapping interface
4. Authentication logs dashboard

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of ALMOE ID OAuth plugin.

== Additional Information ==

For more information about integrating with ALMOE ID, please visit [the ALMOE ID website](https://masjidalmubarokah.com/).

If you need help or have any questions, please contact [ALMOE ID Support](https://masjidalmubarokah.com/support/).