# ALMOE ID OAuth Shortcodes

Plugin ALMOE ID OAuth menyediakan shortcode yang dapat digunakan untuk menampilkan tombol login ALMOE ID di mana saja pada situs WordPress Anda.

## Tombol Login ALMOE ID

Gunakan shortcode berikut untuk menampilkan tombol login ALMOE ID:

```
[almoe_login_button]
```

### Parameter Shortcode

Anda dapat menyesuaikan tampilan dan perilaku tombol dengan parameter berikut:

| Parameter  | Deskripsi | Nilai Default | Opsi |
|------------|-----------|---------------|------|
| `text`     | Teks yang ditampilkan pada tombol | "Login with ALMOE ID" | Teks apapun |
| `redirect` | URL untuk pengalihan setelah login | Halaman saat ini | URL valid |
| `class`    | Kelas CSS tambahan untuk tombol | - | Nama kelas valid |
| `size`     | Ukuran tombol | "normal" | "small", "normal", "large" |
| `fullwidth`| Lebar penuh | "no" | "yes", "no" |

### Contoh Penggunaan

**Tombol Login Dasar:**
```
[almoe_login_button]
```

**Tombol dengan Teks Kustom:**
```
[almoe_login_button text="Masuk dengan ALMOE ID"]
```

**Tombol Ukuran Besar:**
```
[almoe_login_button size="large"]
```

**Tombol Lebar Penuh:**
```
[almoe_login_button fullwidth="yes"]
```

**Pengalihan ke Halaman Tertentu Setelah Login:**
```
[almoe_login_button redirect="https://example.com/dashboard"]
```

**Kombinasi Parameter:**
```
[almoe_login_button text="Masuk Sekarang" size="large" fullwidth="yes" class="my-custom-button"]
```

## Menempatkan Tombol di Template

Anda juga dapat menambahkan tombol login langsung di template theme atau plugin menggunakan fungsi `do_shortcode()`:

```php
<?php echo do_shortcode('[almoe_login_button]'); ?>
```

Atau dengan parameter:

```php
<?php echo do_shortcode('[almoe_login_button text="Masuk" size="large"]'); ?>
```