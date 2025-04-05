<?php
if ($argc < 7) {
    echo "Usage: php setup-plugin.php <plugin_dir> <plugin_name> <description> <function_prefix> <plugin_slug_underscores> <lowercase_prefix>\n";
    exit(1);
}

$plugin_dir              = rtrim($argv[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$plugin_name             = $argv[2];
$description             = $argv[3];
$function_prefix         = $argv[4]; // e.g. "rup_"
$plugin_slug_underscores = $argv[5]; // e.g. "my_plugin"
$lowercase_prefix        = $argv[6]; // e.g. "rup_my_plugin"

// Construct new constant names based on parameters.
$new_VERSION   = "{$function_prefix}{$plugin_slug_underscores}_VERSION";
$new_SLUG      = "{$function_prefix}{$plugin_slug_underscores}_SLUG";
$new_MAIN_FILE = "{$function_prefix}{$plugin_slug_underscores}_MAIN_FILE";
$new_DIR       = "{$function_prefix}{$plugin_slug_underscores}_DIR";
$new_URL       = "{$function_prefix}{$plugin_slug_underscores}_URL";

// Build the search-replace array in a specific order so that longer strings in define() statements get replaced first.
$search_replace = [
    // Replace constant definitions inside define() calls.
    "define('MY_PLUGIN_VERSION'"   => "define('{$new_VERSION}'",
    "define('MY_PLUGIN_SLUG'"      => "define('{$new_SLUG}'",
    "define('MY_PLUGIN_MAIN_FILE'" => "define('{$new_MAIN_FILE}'",
    "define('MY_PLUGIN_DIR'"       => "define('{$new_DIR}'",
    "define('MY_PLUGIN_URL'"       => "define('{$new_URL}'",
    
    // Replace any remaining occurrences of the old constant names.
    "MY_PLUGIN_VERSION"   => $new_VERSION,
    "MY_PLUGIN_SLUG"      => $new_SLUG,
    "MY_PLUGIN_MAIN_FILE" => $new_MAIN_FILE,
    "MY_PLUGIN_DIR"       => $new_DIR,
    "MY_PLUGIN_URL"       => $new_URL,
    
    // Replace function names and hook callbacks.
    "function my_plugin_" => "function {$lowercase_prefix}_",
    "register_activation_hook(__FILE__, 'my_plugin_activate')"   => "register_activation_hook(__FILE__, '{$lowercase_prefix}_activate')",
    "register_deactivation_hook(__FILE__, 'my_plugin_deactivate')" => "register_deactivation_hook(__FILE__, '{$lowercase_prefix}_deactivate')",
    "update_option('my_plugin_activated'"   => "update_option('{$lowercase_prefix}_activated'",
    "delete_option('my_plugin_activated'"   => "delete_option('{$lowercase_prefix}_activated'",
    
    // Replace plugin header and textual placeholders.
    "My Plugin" => $plugin_name,
    "A lightweight WordPress plugin starter template." => $description,
    
    // Replace texts in the licensing/updater block.
    "Your Plugin"           => $plugin_name,
    "your-textdomain"       => strtolower($plugin_slug_underscores) . '-textdomain',
    "your-plugin-menu-slug" => strtolower($plugin_slug_underscores) . '-menu-slug',
    "my-plugin-page"        => strtolower($plugin_slug_underscores) . '-page',
    "my-plugin-deactivation-page" => strtolower($plugin_slug_underscores) . '-deactivation-page'
];

function replace_in_file($file, $search_replace) {
    $content = file_get_contents($file);
    foreach ($search_replace as $search => $replace) {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
        }
    }
    file_put_contents($file, $content);
}

// Iterate recursively over all PHP files in the plugin directory.
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plugin_dir));
foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === "php") {
        replace_in_file($file->getPathname(), $search_replace);
    }
}

echo "Plugin files have been successfully updated.\n";
