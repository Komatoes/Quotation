<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class BackupManagementController extends Controller
{
    /**
     * Check if user is admin (flexible role check)
     */
    private function isAdmin()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Try multiple role check methods
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }
        if (isset($user->role) && strtolower($user->role) === 'admin') {
            return true;
        }
        if (isset($user->role_name) && strtolower($user->role_name) === 'admin') {
            return true;
        }
        return false;
    }

    /**
     * Show backup management dashboard
     */
    public function index()
    {
        // Authorization: Only admin role can access
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized access. Only administrators can manage backups.');
        }

        try {
            // Get all backups from local disk
            $backups = $this->getBackupsList();
            $backupStats = $this->calculateBackupStats($backups);
            $googleDriveStatus = $this->getGoogleDriveStatus();
            
            return view('admin.backup-management', [
                'backups' => $backups,
                'backupStats' => $backupStats,
                'googleDriveConnected' => $googleDriveStatus['connected'],
                'googleDriveFolder' => $googleDriveStatus['folder_id'],
            ]);
        } catch (\Exception $e) {
            return view('admin.backup-management', [
                'backups' => [],
                'backupStats' => [],
                'error' => 'Error loading backups: ' . $e->getMessage(),
                'googleDriveConnected' => false,
            ]);
        }
    }

    /**
     * Trigger backup creation
     */
    public function create(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Run backup
            Artisan::call('backup:run', ['--disable-notifications' => true]);
            
            $output = Artisan::output();
            
            // Upload to Google Drive if connected
            if (config('services.google.drive.enabled')) {
                $this->uploadLatestBackupToGoogleDrive();
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully!',
                'output' => $output,
                'backups' => $this->getBackupsList(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all backups
     */
    private function getBackupsList()
    {
        // Spatie Laravel Backup saves to storage/app/Laravel/
        $backupPath = storage_path('app/Laravel');
        $backups = [];

        if (is_dir($backupPath)) {
            $files = array_diff(scandir($backupPath, SCANDIR_SORT_DESCENDING), ['.', '..']);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $filePath = "$backupPath/$file";
                    $backups[] = [
                        'name' => $file,
                        'size' => $this->formatBytes(filesize($filePath)),
                        'size_bytes' => filesize($filePath),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'path' => $filePath,
                    ];
                }
            }
        }

        return $backups;
    }

    /**
     * Calculate backup statistics for 3-2-1 strategy
     */
    private function calculateBackupStats($backups)
    {
        $total_size = array_sum(array_column($backups, 'size_bytes'));
        $local_count = count($backups);
        $gdrive_count = $this->countGoogleDriveBackups();
        $s3_count = $this->countS3Backups();

        return [
            'local_count' => $local_count,
            'google_drive_count' => $gdrive_count,
            's3_count' => $s3_count,
            'total_size' => $this->formatBytes($total_size),
            'total_size_bytes' => $total_size,
            'strategy_compliant' => $local_count >= 3 && ($gdrive_count > 0 || $s3_count > 0),
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Download a specific backup
     */
    public function download($filename)
    {
        if (!$this->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $backupPath = storage_path('app/Laravel/' . basename($filename));

        if (!file_exists($backupPath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($backupPath);
    }

    /**
     * Delete a backup
     */
    public function delete(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $filename = basename($request->input('filename'));
        $backupPath = storage_path('app/Laravel/' . $filename);

        if (!file_exists($backupPath)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        try {
            unlink($backupPath);
            return response()->json(['success' => true, 'message' => 'Backup deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Google Drive status
     */
    private function getGoogleDriveStatus()
    {
        try {
            $token = config('services.google.drive.access_token');
            $folder_id = config('services.google.drive.folder_id');
            
            return [
                'connected' => !empty($token) && !empty($folder_id),
                'folder_id' => $folder_id,
            ];
        } catch (\Exception $e) {
            return ['connected' => false, 'folder_id' => null];
        }
    }

    /**
     * Count backups on Google Drive
     */
    private function countGoogleDriveBackups()
    {
        // This would use Google Drive API to count backups
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Count backups on S3
     */
    private function countS3Backups()
    {
        // This would use S3 API to count backups
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Restore from a backup file
     */
    public function restore(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $filename = basename($request->input('filename'));
        $backupPath = storage_path('app/Laravel/' . $filename);

        if (!file_exists($backupPath)) {
            return response()->json(['success' => false, 'message' => 'Backup file not found'], 404);
        }

        try {
            Log::info('Restore process started for backup: ' . $filename);

            // Step 1: Extract zip and find SQL file FIRST (before any database operations)
            $extractPath = storage_path('app/restore-temp-' . time());
            @mkdir($extractPath, 0755, true);

            $zip = new \ZipArchive();
            if ($zip->open($backupPath) !== true) {
                Log::error('Failed to open backup zip file: ' . $backupPath);
                throw new \Exception('Failed to open backup zip file. The backup file may be corrupted.');
            }
            $zip->extractTo($extractPath);
            $zip->close();

            // Find the SQL file
            $sqlFile = $this->findSqlFileInDirectory($extractPath);
            if (!$sqlFile) {
                Log::error('No SQL file found in extracted backup at: ' . $extractPath);
                $this->recursiveDelete($extractPath);
                throw new \Exception('No database dump file found in backup');
            }

            Log::info("Found SQL file in backup: $sqlFile");

            // Step 2: Create safety backup BEFORE putting app down
            $safetyBackupPath = storage_path('app/safety-backups');
            @mkdir($safetyBackupPath, 0755, true);

            $safetyFileName = 'pre-restore-' . date('Y-m-d-H-i-s') . '.sql';
            $safetyFilePath = "$safetyBackupPath/$safetyFileName";

            $dbName = env('DB_DATABASE', 'quotation');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            $dbHost = env('DB_HOST', 'localhost');

            Log::info('Creating safety backup to: ' . $safetyFilePath);
            $this->createDatabaseDump($safetyFilePath, $dbHost, $dbUser, $dbPass, $dbName);

            // Step 3: Put application in maintenance mode
            Artisan::call('down', ['--secret' => uniqid()]);
            Log::info('Application put into maintenance mode');

            try {
                // Step 4: Import SQL file into database using PDO for better compatibility
                Log::info('Starting database restore from SQL file');
                $this->restoreDatabaseFromSql($sqlFile, $dbHost, $dbUser, $dbPass, $dbName);
                Log::info('Database restored successfully from backup: ' . $filename);

                // Step 5: Clear Laravel caches
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Log::info('Caches cleared after restore');

                // Step 6: Bring application back online
                Artisan::call('up');
                Log::info('Application brought back online after restore');

                // Step 7: Cleanup temp files
                try {
                    $this->recursiveDelete($extractPath);
                    Log::info('Temp files cleaned up');
                } catch (\Exception $e) {
                    Log::warning("Cleanup warning (non-critical): " . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Database restored successfully from ' . $filename . '. Safety backup saved as: ' . $safetyFileName,
                    'safety_backup' => $safetyFileName,
                ]);

            } catch (\Exception $e) {
                // Restore from safety backup on error
                Log::error('Restore failed, attempting rollback. Error: ' . $e->getMessage());
                try {
                    $this->restoreDatabaseFromSql($safetyFilePath, $dbHost, $dbUser, $dbPass, $dbName);
                    Log::info('Successfully rolled back to safety backup');
                } catch (\Exception $rollbackError) {
                    Log::error('Rollback also failed: ' . $rollbackError->getMessage());
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Restore operation failed: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            
            // Ensure app is brought back online
            try {
                Artisan::call('up');
            } catch (\Exception $upError) {
                Log::error('Failed to bring app back online: ' . $upError->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create database dump using mysqldump command
     */
    private function createDatabaseDump($outputPath, $dbHost, $dbUser, $dbPass, $dbName)
    {
        // Prefer using mysqldump if available, otherwise fallback to a PDO-based dumper
        if (function_exists('exec')) {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $mysqlBin = $isWindows ? 'C:\\\\xampp\\\\mysql\\\\bin\\\\mysqldump.exe' : '/usr/bin/mysqldump';

            $cmd = $isWindows ? '"' . $mysqlBin . '"' : $mysqlBin;
            $cmd .= ' --host=' . escapeshellarg($dbHost);
            $cmd .= ' --user=' . escapeshellarg($dbUser);
            if (!empty($dbPass)) {
                $cmd .= ' --password=' . escapeshellarg($dbPass);
            }
            $cmd .= ' ' . escapeshellarg($dbName);
            $cmd .= ' > ' . escapeshellarg($outputPath) . ' 2>&1';

            Log::info('Executing mysqldump command');
            exec($cmd, $output, $status);

            if ($status !== 0) {
                Log::error('Mysqldump failed: ' . implode("\n", $output));
                throw new \Exception('Failed to create database dump: ' . implode("\n", $output));
            }

            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                Log::error('Database dump file not created or empty: ' . $outputPath);
                throw new \Exception('Database dump was not created properly');
            }

            Log::info('Database dump created successfully: ' . $outputPath . ' (Size: ' . filesize($outputPath) . ' bytes)');
            return;
        }

        // Fallback: create dump via PDO (pure PHP) when exec() is disabled on the host
        Log::warning('exec() not available, creating database dump via PDO (slower)');
        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdo = new \PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $fh = fopen($outputPath, 'w');
            if ($fh === false) {
                throw new \Exception('Unable to open output file for writing: ' . $outputPath);
            }

            fwrite($fh, "-- PHP PDO database dump\n-- Database: $dbName\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n");

            // Get all tables
            $tables = [];
            $tablesStmt = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            while ($row = $tablesStmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                fwrite($fh, "--\n-- Table structure for table `$table`\n--\n\n");
                $createStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(\PDO::FETCH_ASSOC);
                $createSql = isset($createStmt['Create Table']) ? $createStmt['Create Table'] : array_values($createStmt)[1];
                fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($fh, $createSql . ";\n\n");

                // Dump table data in batches
                fwrite($fh, "--\n-- Dumping data for table `$table`\n--\n\n");
                $colStmt = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                $columns = array_column($colStmt, 'Field');
                $colList = array_map(function ($c) { return "`$c`"; }, $columns);
                $colSql = implode(', ', $colList);

                $dataStmt = $pdo->query("SELECT * FROM `$table`");
                $batch = [];
                $rowCount = 0;
                while ($row = $dataStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $values = [];
                    foreach ($columns as $col) {
                        if (!isset($row[$col]) || $row[$col] === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($row[$col]);
                        }
                    }
                    $batch[] = '(' . implode(', ', $values) . ')';
                    $rowCount++;

                    if (count($batch) >= 100) {
                        fwrite($fh, "INSERT INTO `$table` ($colSql) VALUES\n" . implode(",\n", $batch) . ";\n\n");
                        $batch = [];
                    }
                }
                if (!empty($batch)) {
                    fwrite($fh, "INSERT INTO `$table` ($colSql) VALUES\n" . implode(",\n", $batch) . ";\n\n");
                }
                Log::info("Dumped $rowCount rows from table $table");
            }

            fclose($fh);
            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                throw new \Exception('PDO dump failed or produced empty file');
            }
            Log::info('PDO-based database dump created: ' . $outputPath . ' (Size: ' . filesize($outputPath) . ' bytes)');
        } catch (\Exception $e) {
            Log::error('PDO-based dump failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore database from SQL file using PDO (more compatible with Hostinger)
     */
    private function restoreDatabaseFromSql($sqlFile, $dbHost, $dbUser, $dbPass, $dbName)
    {
        if (!file_exists($sqlFile)) {
            throw new \Exception('SQL file not found: ' . $sqlFile);
        }

        // Use streaming read to avoid loading very large SQL files into memory
        try {
            $handle = new \SplFileObject($sqlFile, 'r');

            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            try {
                $pdo = new \PDO($dsn, $dbUser, $dbPass, [\PDO::MYSQL_ATTR_MULTI_STATEMENTS => false]);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                Log::info('Database connection established');
            } catch (\PDOException $e) {
                Log::error('Failed to connect to database: ' . $e->getMessage());
                throw new \Exception('Database connection failed: ' . $e->getMessage());
            }

            $statement = '';
            $executed = 0;

            while (!$handle->eof()) {
                $line = $handle->fgets();

                // Skip comments and mysql dump delimiter directives
                if (preg_match('/^\s*(?:--|#)/', $line)) {
                    continue;
                }
                $trim = ltrim($line);
                if (stripos($trim, 'DELIMITER ') === 0) {
                    // Skip DELIMITER lines (procedures are not supported by this simple importer)
                    $statement = '';
                    continue;
                }

                $statement .= $line;

                // If line ends with semicolon (statement terminator), execute
                if (preg_match('/;\s*$/', $line)) {
                    $stmt = trim($statement);
                    if ($stmt !== '') {
                        try {
                            $pdo->exec($stmt);
                            $executed++;
                            if ($executed % 100 === 0) {
                                Log::info("Executed $executed statements...");
                            }
                        } catch (\PDOException $e) {
                            Log::error('Failed to execute statement: ' . $e->getMessage());
                            Log::error('Statement excerpt: ' . substr($stmt, 0, 200));
                            throw new \Exception('SQL execution error: ' . $e->getMessage());
                        }
                    }
                    $statement = '';
                }
            }

            // If any remaining statement exists without trailing semicolon, execute it
            if (trim($statement) !== '') {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (\PDOException $e) {
                    Log::error('Failed to execute final statement: ' . $e->getMessage());
                    Log::error('Final statement excerpt: ' . substr($statement, 0, 200));
                    throw new \Exception('SQL execution error: ' . $e->getMessage());
                }
            }

            Log::info("Successfully executed $executed SQL statements (streamed)");

        } catch (\Exception $e) {
            Log::error('Database restore error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find SQL file in directory recursively
     */
    private function findSqlFileInDirectory($directory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Restore database from safety backup
     */
    private function restoreFromSafetyBackup($safetyBackupPath, $dbHost, $dbUser, $dbPass, $dbName)
    {
        try {
            // Prefer using PDO-based restore (no shell) to avoid disabled exec() on some hosts
            if (function_exists('exec')) {
                // If exec is available, we could use mysql client, but prefer PDO restore to be consistent
                $this->restoreDatabaseFromSql($safetyBackupPath, $dbHost, $dbUser, $dbPass, $dbName);
            } else {
                $this->restoreDatabaseFromSql($safetyBackupPath, $dbHost, $dbUser, $dbPass, $dbName);
            }
            Log::info('Database rolled back to safety backup via PDO');
        } catch (\Exception $e) {
            Log::error('Error during rollback: ' . $e->getMessage());
        }
    }

    /**
     * Recursively delete directory
     */
    private function recursiveDelete($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $fileinfo) {
            $path = $fileinfo->getRealPath();
            try {
                if ($fileinfo->isDir()) {
                    @rmdir($path);
                } else {
                    // Try to delete file, suppress errors for locked files
                    @unlink($path);
                    
                    // If file still exists (locked), log it but don't fail
                    if (file_exists($path)) {
                        Log::warning("Could not delete locked file: $path");
                    }
                }
            } catch (\Exception $e) {
                // Log but don't fail on individual file errors
                Log::warning("Error deleting file during cleanup: $path - " . $e->getMessage());
            }
        }

        // Try to remove directory, suppress errors
        @rmdir($directory);
        
        // Final cleanup: force delete using Windows command if needed
        if (is_dir($directory)) {
            try {
                exec('rmdir /s /q ' . escapeshellarg($directory), $output, $status);
                if ($status === 0) {
                    Log::info("Forced directory deletion succeeded: $directory");
                } else {
                    Log::warning("Forced directory deletion failed: $directory");
                }
            } catch (\Exception $e) {
                Log::warning("Error during forced directory deletion: " . $e->getMessage());
            }
        }
    }

    /**
     * Upload latest backup to Google Drive
     */
    private function uploadLatestBackupToGoogleDrive()
    {
        try {
            if (!config('services.google.drive.enabled')) {
                Log::info('Google Drive backup disabled');
                return;
            }

            $backups = $this->getBackupsList();
            if (empty($backups)) {
                Log::warning('No backups found to upload to Google Drive');
                return;
            }

            $latestBackup = $backups[0];
            $filePath = $latestBackup['path'];
            $fileName = $latestBackup['name'];

            Log::info("Uploading backup to Google Drive: $fileName");

            // Get Google Drive credentials
            $projectId = config('services.google.drive.project_id');
            $privateKeyId = config('services.google.drive.private_key_id');
            $privateKey = config('services.google.drive.private_key');
            $clientEmail = config('services.google.drive.client_email');
            $clientId = config('services.google.drive.client_id');
            $folderId = config('services.google.drive.folder_id');

            if (!$projectId || !$privateKey || !$clientEmail || !$folderId) {
                throw new \Exception('Google Drive credentials not fully configured in .env');
            }

            // Get access token
            $accessToken = $this->getGoogleDriveAccessToken(
                $clientEmail,
                $privateKey,
                $projectId
            );

            if (!$accessToken) {
                throw new \Exception('Failed to obtain Google Drive access token');
            }

            // Upload file to Google Drive
            $this->uploadFileToGoogleDrive(
                $filePath,
                $fileName,
                $folderId,
                $accessToken
            );

            Log::info("Successfully uploaded backup to Google Drive: $fileName");
        } catch (\Exception $e) {
            Log::error('Google Drive backup upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Get Google Drive access token using service account
     */
    private function getGoogleDriveAccessToken($clientEmail, $privateKey, $projectId)
    {
        try {
            $now = time();
            $expiry = $now + 3600;

            // Create JWT claim
            $payload = [
                'iss' => $clientEmail,
                'sub' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/drive.file',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $expiry,
                'iat' => $now,
            ];

            // Create JWT header
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];

            // Encode JWT
            $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
            $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
            $signatureInput = "$headerEncoded.$payloadEncoded";

            // Sign JWT with private key
            $privateKeyResource = openssl_pkey_get_private($privateKey);
            if (!$privateKeyResource) {
                throw new \Exception('Failed to load private key');
            }

            openssl_sign($signatureInput, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
            $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $jwt = "$signatureInput.$signatureEncoded";

            // Exchange JWT for access token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('Google OAuth token request failed: ' . $response);
                return null;
            }

            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error getting Google Drive access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload file to Google Drive
     */
    private function uploadFileToGoogleDrive($filePath, $fileName, $folderId, $accessToken)
    {
        try {
            $fileSize = filesize($filePath);
            Log::info("Uploading file to Google Drive: $fileName (Size: " . $this->formatBytes($fileSize) . ")");

            // Create metadata
            $metadata = json_encode([
                'name' => $fileName,
                'parents' => [$folderId],
                'description' => 'Database backup from Quotation app - ' . date('Y-m-d H:i:s'),
            ]);

            // Upload file using multipart upload
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: multipart/related; boundary=foo_bar_baz',
            ]);

            // Build multipart body
            $body = "--foo_bar_baz\r\n";
            $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
            $body .= $metadata . "\r\n\r\n";
            $body .= "--foo_bar_baz\r\n";
            $body .= "Content-Type: application/zip\r\n\r\n";
            $body .= file_get_contents($filePath);
            $body .= "\r\n--foo_bar_baz--";

            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600); // 10 minutes for large files

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                Log::info("File uploaded to Google Drive with ID: " . ($data['id'] ?? 'unknown'));
                return true;
            } else {
                Log::error("Google Drive upload failed (HTTP $httpCode): $response");
                throw new \Exception("Upload failed with HTTP code $httpCode");
            }
        } catch (\Exception $e) {
            Log::error('Error uploading to Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }
}
