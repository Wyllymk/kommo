<?php

// Configuration
$plugin_slug = 'kommo';
$source_dir = __DIR__;
$build_dir = __DIR__ . '/build';
$zip_file = __DIR__ . '/dist/' . $plugin_slug . '.zip';

// Files and directories to exclude from production build
$exclude = [
    '.git',
    '.github',
    'node_modules',
    'tests',
    'vendor/composer/installed.php',
    'vendor/composer/installed.json',
    'vendor/composer/installed-dev.php',
    'vendor/composer/installed-dev.json',
    '.gitignore',
    '.editorconfig',
    'phpunit.xml',
    'phpcs.xml',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'webpack.config.js',
    'build.php',
    'README.md',
    '.DS_Store',
    'build',
    'dist'
];

// Create build and dist directories if they don't exist
if (!file_exists($build_dir)) {
    mkdir($build_dir, 0755, true);
}
if (!file_exists(__DIR__ . '/dist')) {
    mkdir(__DIR__ . '/dist', 0755, true);
}

// Clean up previous build
if (file_exists($build_dir)) {
    error_log("Cleaning up previous build...\n");
    recursiveRemove($build_dir);
    mkdir($build_dir, 0755, true);
}

// Remove previous zip if it exists
if (file_exists($zip_file)) {
    error_log("Removing previous zip file...\n");
    unlink($zip_file);
}

// Copy files to build directory
error_log("Copying files to build directory...\n");
recursiveCopy($source_dir, $build_dir, $exclude);

// Run composer install --no-dev for production dependencies only
error_log("Installing production dependencies...\n");
shell_exec('cd ' . escapeshellarg($build_dir) . ' && composer install --no-dev --optimize-autoloader');

// Create zip file
error_log("Creating zip file...\n");
createZip($build_dir, $zip_file);

error_log("Build completeded! Production zip created at: $zip_file\n");

// Helper Functions
function recursiveCopy($src, $dst, $exclude) {
    $dir = opendir($src);
    @mkdir($dst);
    
    while ($file = readdir($dir)) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            // Skip excluded files and directories
            if (shouldExclude($srcFile, $exclude)) {
                continue;
            }
            
            if (is_dir($srcFile)) {
                recursiveCopy($srcFile, $dstFile, $exclude);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }
    closedir($dir);
}

function recursiveRemove($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? recursiveRemove($path) : unlink($path);
    }
    return rmdir($dir);
}

function shouldExclude($file, $exclude) {
    foreach ($exclude as $pattern) {
        if (strpos($file, '/' . $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function createZip($source, $destination) {
    if (file_exists($destination)) {
        unlink($destination);
    }
    
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $source = str_replace('\\', '/', realpath($source));
        
        if (is_dir($source)) {
            $iterator = new RecursiveDirectoryIterator($source);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);
                
                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    $zip->addFile($file, str_replace($source . '/', '', $file));
                }
            }
        }
        
        $zip->close();
    }
}