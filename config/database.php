<?php
/**
 * Konfigurasi Koneksi Database MySQL menggunakan PDO
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_produk_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Mendapatkan koneksi PDO tunggal (Singleton pattern)
 * 
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Catat detail error ke log file server (tanpa menampilkan kredensial/error sensitif ke pengguna)
            $logMessage = sprintf("[%s] Database Connection Error: %s in %s:%d\n", 
                date('Y-m-d H:i:s'), 
                $e->getMessage(), 
                $e->getFile(), 
                $e->getLine()
            );
            error_log($logMessage);

            // Opsional: Simpan juga ke file log lokal di folder logs/
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents($logDir . '/database_error.log', $logMessage, FILE_APPEND);

            // Tampilkan pesan error ramah tanpa detail teknis sensitif
            http_response_code(500);
            die("<div style='padding: 24px; background: #fee2e2; color: #991b1b; font-family: system-ui, sans-serif; border-radius: 10px; margin: 40px auto; max-width: 600px; border: 1px solid #f87171; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                    <h3 style='margin-top:0; font-size: 1.25rem; font-weight: 700;'>Terjadi Kesalahan Server</h3>
                    <p style='margin-bottom:0; line-height: 1.5;'>Maaf, sistem tidak dapat terhubung ke basis data saat ini. Detail kesalahan telah dicatat untuk penanganan teknis. Silakan coba beberapa saat lagi.</p>
                 </div>");
        }
    }

    return $pdo;
}
