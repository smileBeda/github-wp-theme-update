<?php
/**
 * Require the GitHub_Theme_Updater class.
 *
 * Your theme will require GitHub_Theme_Updater to be loaded under a custom namespace.
 * Ideally use an autoloader, but for the sake of functionality we are using require_once here.
 */
require_once get_template_directory() . 'update.php';
use MyTheme\GitHub_Theme_Updater;
( new GitHub_Theme_Updater( plugin_basename( __FILE__ ), '1.0.0', 'https://api.github.com/repos/smileBeda/github-wp-theme-update' ) )->init();
