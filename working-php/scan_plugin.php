<?php
// Get extracted directory from arguments
if ($argc < 2) {
    die("Usage: php scan_plugin.php <extracted_dir>\n");
}

$plugin_dir = $argv[1];
$metadata_file = "$plugin_dir/plugin_metadata.txt";

// Function to find the main plugin file (recursively)
function find_plugin_file($dir) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if (preg_match('/\.php$/', $file) && is_file($file)) {
            $content = file_get_contents($file);
            if (preg_match('/Plugin Name:\s*(.+)/i', $content)) {
                return $file; // Found main plugin file
            }
        }
    }
    return null;
}

// Find the main plugin file inside the extracted folder
$plugin_file = find_plugin_file($plugin_dir);

if (!$plugin_file) {
    file_put_contents($metadata_file, "Error: No valid plugin file found.");
    die("Error: No valid plugin file found.\n");
}

// Extract plugin header info
$plugin_info = [];
$headers = [
    'Plugin Name'       => 'PluginName',
    'Version'           => 'Version',
    'Requires at least' => 'Requires',
    'Tested up to'      => 'Tested',
    'Requires PHP'      => 'PHP',
    'Author'            => 'Author',
    'Author URI'        => 'AuthorURI',
    'Description'       => 'Description'
];

$content = file_get_contents($plugin_file);
foreach ($headers as $key => $field) {
    if (preg_match("/$key:\s*(.+)/i", $content, $matches)) {
        $plugin_info[$field] = trim($matches[1]);
    }
}

// Set defaults if not found
$plugin_info += [
    'PluginName'  => 'Unknown Plugin',
    'Version'     => '0.0.0',
    'Requires'    => 'N/A',
    'Tested'      => 'N/A',
    'PHP'         => 'N/A',
    'Author'      => 'Really Useful Plugins',
    'AuthorURI'   => 'https://reallyusefulplugins.com',
    'Description' => 'No description provided.'
];

// Save metadata to a file for batch script
$metadata_output = "";
foreach ($plugin_info as $key => $value) {
    $metadata_output .= "$key:$value\n";
}
file_put_contents($metadata_file, $metadata_output);

echo "✅ Plugin metadata extracted successfully from: $plugin_file\n";
?>
