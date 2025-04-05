<?php
// Get file paths from command-line arguments
if ($argc < 4) {
    die("Usage: php convert.php <json_file> <backup_file> <changelog_file>\n");
}

$json_file = $argv[1];
$backup_file = $argv[2];
$changelog_file = $argv[3];

// Ensure the JSON file exists
if (!file_exists($json_file)) {
    die("Error: release.json not found.\n");
}

// Read and clean JSON data
$json_raw = file_get_contents($json_file);
$json_raw = preg_replace('/[\x00-\x1F\x7F]/', '', $json_raw); // Remove control characters
$json_data = json_decode($json_raw, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid JSON format - " . json_last_error_msg() . "\n");
}

// Display current version info
echo "Current Version: {$json_data['version']}\n";
echo "Current Requires: {$json_data['requires']}\n";
echo "Current Tested: {$json_data['tested']}\n";
echo "Current Requires PHP: {$json_data['requires_php']}\n";

// Function to prompt for changes
function promptChange($current_value, $label) {
    echo "Enter new $label (press Enter to keep [$current_value]): ";
    $new_value = trim(fgets(STDIN));
    return $new_value !== "" ? $new_value : $current_value;
}

// Ask for changes
$json_data['version'] = promptChange($json_data['version'], "Version");
$json_data['requires'] = promptChange($json_data['requires'], "Requires");
$json_data['tested'] = promptChange($json_data['tested'], "Tested");
$json_data['requires_php'] = promptChange($json_data['requires_php'], "Requires PHP");

// Ensure changelog file exists
if (!file_exists($changelog_file)) {
    die("Error: changelog.txt not found.\n");
}

// Read and parse changelog.txt
$changelog_content = file_get_contents($changelog_file);
$changelog_content = mb_convert_encoding($changelog_content, 'UTF-8', 'auto'); // Ensure UTF-8 encoding

$entries = preg_split('/=\s*([\d\.]+)\s*\(([^)]+)\)\s*=/', $changelog_content, -1, PREG_SPLIT_DELIM_CAPTURE);

$new_changelog = "";

for ($i = 1; $i < count($entries); $i += 3) {
    $version = trim($entries[$i]);
    $date = trim($entries[$i + 1]);
    $changes = trim($entries[$i + 2]);

    $changes_list = explode("\n", $changes);
    $new_changelog .= "<h4>$version $date</h4>\n<ul>\n";

    foreach ($changes_list as $change) {
        $change = trim($change);
        if (!empty($change)) {
            $change = preg_replace('/^(New|Fixed|Tweaked|Improvement):\s*/', '<strong>$1:</strong> ', $change);
            $new_changelog .= "<li>$change</li>\n";
        }
    }

    $new_changelog .= "</ul>\n";
}

// Backup old JSON
copy($json_file, $backup_file);

// **Important Fix**: Ensure Proper JSON Encoding
$json_data['sections']['changelog'] = $new_changelog;

// Save updated JSON safely
file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Updated release.json successfully! A backup has been saved as previous.json.\n";
?>
