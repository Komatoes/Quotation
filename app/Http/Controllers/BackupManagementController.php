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
            // Step 1: Create safety backup of current database
            $safetyBackupPath = storage_path('app/safety-backups');
            if (!is_dir($safetyBackupPath)) {
                mkdir($safetyBackupPath, 0755, true);
            }

            $safetyFileName = 'pre-restore-' . date('Y-m-d-H-i-s') . '.sql';
            $safetyFilePath = "$safetyBackupPath/$safetyFileName";

            $mysqlBin = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            $dbName = env('DB_DATABASE', 'quotation');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            $dbHost = env('DB_HOST', 'localhost');

            // Build mysqldump command
            $cmd = '"' . $mysqlBin . '" --host=' . $dbHost . ' --user=' . $dbUser;
            if (!empty($dbPass)) {
                $cmd .= ' --password=' . escapeshellarg($dbPass);
            }
            $cmd .= ' ' . $dbName . ' > ' . escapeshellarg($safetyFilePath);

            // Execute safety backup
            exec($cmd . ' 2>&1', $output, $status);
            if ($status !== 0) {
                throw new \Exception('Failed to create safety backup: ' . implode("\n", $output));
            }

            Log::info("Safety backup created: $safetyFileName");

            // Step 2: Extract zip and find SQL file
            $extractPath = storage_path('app/restore-temp-' . time());
            mkdir($extractPath, 0755, true);

            try {
                $zip = new \ZipArchive();
                if ($zip->open($backupPath) !== true) {
                    throw new \Exception('Failed to open backup zip file');
                }
                $zip->extractTo($extractPath);
                $zip->close();

                // Find the SQL file
                $sqlFile = $this->findSqlFileInDirectory($extractPath);
                if (!$sqlFile) {
                    throw new \Exception('No database dump file found in backup');
                }

                Log::info("Found SQL file in backup: $sqlFile");

                // Step 3: Put application in maintenance mode
                Artisan::call('down', ['--secret' => uniqid()]);
                Log::info('Application put into maintenance mode');

                // Step 4: Import SQL file into database
                $importCmd = '"' . 'C:\\xampp\\mysql\\bin\\mysql.exe' . '" --host=' . $dbHost . ' --user=' . $dbUser;
                if (!empty($dbPass)) {
                    $importCmd .= ' --password=' . escapeshellarg($dbPass);
                }
                $importCmd .= ' ' . $dbName . ' < ' . escapeshellarg($sqlFile);

                exec($importCmd . ' 2>&1', $importOutput, $importStatus);
                if ($importStatus !== 0) {
                    // Restore from safety backup on error
                    $this->restoreFromSafetyBackup($safetyFilePath, $dbHost, $dbUser, $dbPass, $dbName);
                    throw new \Exception('Database restore failed. Rolled back to previous state. Error: ' . implode("\n", $importOutput));
                }

                Log::info('Database restored from backup: ' . $filename);

                // Step 5: Clear Laravel caches
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');

                // Step 6: Bring application back online
                Artisan::call('up');
                Log::info('Application brought back online after restore');

                // Step 7: Cleanup temp files (don't fail if cleanup has issues)
                try {
                    $this->recursiveDelete($extractPath);
                } catch (\Exception $e) {
                    // Log cleanup error but don't fail the restore
                    Log::warning("Cleanup failed (non-critical): " . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Database restored successfully from ' . $filename . '. Safety backup saved as: ' . $safetyFileName,
                    'safety_backup' => $safetyFileName,
                ]);
            } catch (\Exception $e) {
                // Make sure to bring app back up on error
                Artisan::call('up');
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Restore failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
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
            $restoreCmd = '"C:\\xampp\\mysql\\bin\\mysql.exe" --host=' . $dbHost . ' --user=' . $dbUser;
            if (!empty($dbPass)) {
                $restoreCmd .= ' --password=' . escapeshellarg($dbPass);
            }
            $restoreCmd .= ' ' . $dbName . ' < ' . escapeshellarg($safetyBackupPath);

            exec($restoreCmd . ' 2>&1', $output, $status);
            if ($status === 0) {
                Log::info('Database rolled back to safety backup');
            } else {
                Log::error('Failed to restore from safety backup: ' . implode("\n", $output));
            }
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
            $backups = $this->getBackupsList();
            if (empty($backups)) {
                return;
            }

            $latestBackup = $backups[0];
            // Google Drive upload logic would go here
            // For now, this is a placeholder
            Log::info('Google Drive backup upload initiated for: ' . $latestBackup['name']);
        } catch (\Exception $e) {
            Log::error('Google Drive backup upload failed: ' . $e->getMessage());
        }
    }
}
