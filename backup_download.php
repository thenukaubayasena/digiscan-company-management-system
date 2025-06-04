<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Your MySQL password (if any)
$dbName = 'digiscan_db';

$timestamp = date('Ymd_His');
$backupFile = "backup_$timestamp.sql";
$backupPath = __DIR__ . DIRECTORY_SEPARATOR . $backupFile;

// Path to mysqldump (adjust if needed)
$mysqldump = '"C:\\xampp\\mysql\\bin\\mysqldump.exe"';

$command = "$mysqldump -h $dbHost -u $dbUser " . ($dbPass ? "-p$dbPass " : '') . "$dbName > \"$backupPath\"";

// Execute
exec($command, $output, $result);

// Success
if ($result === 0 && file_exists($backupPath)) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($backupPath) . '"');
    header('Content-Length: ' . filesize($backupPath));
    readfile($backupPath);
    unlink($backupPath);
    exit;
} else {
    echo "❌ Backup failed. Please check path to mysqldump, credentials, and permissions.";
}
?>
