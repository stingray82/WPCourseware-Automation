<?php
// Get the changelog file path from command-line argument
if ($argc < 2) {
    die("Error: No changelog file provided.\n");
}

$changelog_file = $argv[1];
$release_file = 'release.html';

// Ensure the file exists
if (!file_exists($changelog_file)) {
    die("Error: Changelog file not found: $changelog_file\n");
}

// Read the file contents
$contents = file_get_contents($changelog_file);

// Split entries based on versions
$entries = preg_split('/=\s*([\d\.]+)\s*\(([^)]+)\)\s*=/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);

$html_output = "";

for ($i = 1; $i < count($entries); $i += 3) {
    $version = trim($entries[$i]);
    $date = trim($entries[$i + 1]);
    $changes = trim($entries[$i + 2]);

    $changes_list = explode("\n", $changes);
    $html_output .= "<h2>$version</h2>\n<ul>\n";

    foreach ($changes_list as $change) {
        $change = trim($change);
        if (!empty($change)) {
            $change = preg_replace('/^(New|Fixed|Tweaked|Improvement):\s*/', '<strong>$1:</strong> ', $change);
            $html_output .= "<li>$change</li>\n";
        }
    }

    $html_output .= "</ul>\n";
}

// Write to release.html
file_put_contents($release_file, $html_output);

// Display success message
//echo "Successfully generated release.html\n";
?>
