<?php
// Define default file name
$json_file = "release.json";

// If an old release.json exists, back it up
if (file_exists($json_file)) {
    copy($json_file, "previous.json");
    echo "Backup of previous release.json saved as previous.json.\n";
}

// Prompt user for plugin details
function promptInput($message, $default = "") {
    echo $message . ($default ? " [$default]" : "") . ": ";
    $input = trim(fgets(STDIN));
    return $input !== "" ? $input : $default;
}

// Collect Plugin Details
$plugin_name = promptInput("Enter Plugin Name", "My New Plugin");
$plugin_slug = strtolower(str_replace(" ", "-", $plugin_name)); // Generate slug
$author_name = promptInput("Enter Author Name", "Really Useful Plugins");
$author_url = promptInput("Enter Author URL", "https://reallyusefulplugins.com");
$version = promptInput("Enter Initial Version", "1.0.0");
$requires_wp = promptInput("Enter Minimum WordPress Version", "6.5");
$tested_wp = promptInput("Enter Tested up to WordPress Version", "6.7.1");
$requires_php = promptInput("Enter Minimum PHP Version", "7.4");
$plugin_description = promptInput("Enter Plugin Description", "This is a brand-new plugin.");

// Default Changelog Entry
$initial_changelog = "<h4>$version - " . date("d/m/Y") . "</h4>\n<ul>\n<li>Initial release.</li>\n</ul>";

// Construct the JSON Data
$json_data = [
    "name" => $plugin_name,
    "slug" => $plugin_slug,
    "author" => "<a href='$author_url'>$author_name</a>",
    "author_profile" => $author_url,
    "version" => $version,
    "requires" => $requires_wp,
    "tested" => $tested_wp,
    "requires_php" => $requires_php,
    "sections" => [
        "description" => $plugin_description,
        "changelog" => $initial_changelog
    ]
];

// Write JSON File
file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "✅ New release.json generated successfully!\n";
?>
