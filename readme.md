# Vigihdev Encryption

Powerful and easy-to-use encryption and decryption library for PHP.

## Installation

```bash
composer require vigihdev/encryption
```

## Usage

This library provides a simple way to encrypt and decrypt your environment files.

### Key Generation

First, you need to generate a key. You can do this by running the following command:

```bash
php vendor/bin/encryption defuse:key-generator
```

This will generate a key and store it in a file named `defuse.key` in your project's root directory.

### Encrypting Environment Files

To encrypt an environment file, you can use the `environment:encrypt` command:

```bash
php vendor/bin/encryption environment:encrypt
```

This command will encrypt your `.env` file and create a `.env.encrypted` file.

### Decrypting Environment Files

To decrypt an environment file, you can use the `environment:decrypt` command:

```bash
php vendor/bin/encryption environment:decrypt
```

This command will decrypt your `.env.encrypted` file and create a `.env.decrypted` file.

## Konfigurasi

Pustaka ini mengandalkan konfigurasi layanan untuk mengelola kunci enkripsi dan proses enkripsi/dekripsi environment. Berikut adalah contoh konfigurasi yang bisa Anda gunakan, misalnya di `config/packages/security.yaml` jika Anda menggunakan Symfony:

```yaml
# config/packages/security.yaml
services:
  Vigihdev\Encryption\Contracts\KeyServiceContract:
    public: false
    class: 'Vigihdev\Encryption\Service\KeyService'
    arguments:
      $keyFilepath: "%env(DEFUSE_KEY)%"

  Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract:
    public: true
    class: 'Vigihdev
    Encryption\Service\EnvironmentEncryptorService'
    arguments:
      $keyService: '@Vigihdev\Encryption\Contracts\KeyServiceContract'
```

**Penjelasan Konfigurasi:**

*   **`Vigihdev\Encryption\Contracts\KeyServiceContract`**: Layanan ini bertanggung jawab untuk mengelola kunci enkripsi. `keyFilepath` diatur melalui variabel lingkungan `DEFUSE_KEY`. Pastikan variabel lingkungan ini menunjuk ke lokasi file kunci Anda (misalnya, `DEFUSE_KEY=./defuse.key`).
*   **`Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract`**: Layanan ini menangani logika enkripsi dan dekripsi file environment. Ia bergantung pada `KeyServiceContract` untuk mendapatkan kunci yang diperlukan.

## Perintah yang Tersedia

*   `defuse:key-generator`: Membuat kunci enkripsi baru.
*   `defuse:show-key`: Menampilkan kunci enkripsi.
*   `environment:encrypt`: Mengenkripsi file environment.
*   `environment:decrypt`: Mendekripsi file environment.

## Pengujian

Untuk menjalankan pengujian, Anda bisa menggunakan perintah berikut:

```bash
composer test
```

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT.
