<?php

/**
 * Neuron AI Autoloader
 * 
 * Autoloads PerfexChat Neuron classes.
 */

spl_autoload_register(function ($class) {
    // Only handle PerfexChat\Neuron namespace
    $prefix = 'PerfexChat\\Neuron\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, strlen($prefix));

    // Convert namespace to file path
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load global helper functions (ai_config, etc.)
$aiConfig = __DIR__ . '/config/ai_config.php';
if (file_exists($aiConfig)) {
    require_once $aiConfig;
}

// Also require the NeuronAI vendor autoloader
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}
