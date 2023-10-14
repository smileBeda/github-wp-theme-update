<?php

class GitHub_Theme_Updater {

private $theme_slug;
private $current_version;
private $github_repo_url;

public function __construct( $theme_slug, $current_version, $github_repo_url ) {

	$this->theme_slug     = $theme_slug;
	$this->current_version = $current_version;
	$this->github_repo_url = $github_repo_url;
}

public function init() {
	add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_updates' ) );
	add_action( 'admin_init', array( $this, 'force_github_update_check' ) );
}

public function force_github_update_check() {

	global $pagenow;
	if ( $this->check_caps() ) {
		delete_site_transient( 'update_themes' );
		wp_safe_redirect( admin_url( 'themes.php' ) );
		exit;rf
	}
}

public function check_for_updates( $transient ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$release = $this->get_github_release_info();
	if ( isset( $release['assets'][0]['browser_download_url'] ) ) {
		$package = $release['assets'][0]['browser_download_url'];
	} else {
		return $transient;
	}

	if ( is_array( $release )
		&& isset( $release['tag_name'] )
		&& version_compare( $release['tag_name'], $this->current_version, '>' )
	) {
		$transient->response[ $this->theme_slug ] = array(
			'theme'       => $this->theme_slug,
			'new_version' => $release['tag_name'],
			'package'     => $package,
			'url'         => $this->github_repo_url,
		);
	}

	return $transient;
}

private function get_github_release_info() {
	$url = $this->github_repo_url . '/releases/latest';

	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	return json_decode( $body, true );
}

private function check_caps() {

	global $pagenow;

	if ( 'themes.php' === $pagenow
		&& current_user_can( 'update_themes' )
		&& isset( $_GET['force-github-update'] )
		&& 'true' === $_GET['force-github-update']
		&& isset( $_GET['_wpnonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'github-updater-check' )
	) {
		return true;
	}

	return false;
}
}
