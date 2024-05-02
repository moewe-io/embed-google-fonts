<?php

class Embed_Google_Fonts_Administration {
	function __construct() {
		add_action( 'wp_ajax_embed_google_fonts_copy_files', [ $this, 'copy_files' ] );
		add_filter( 'plugin_row_meta', [ $this, 'init_row_meta' ], 11, 2 );
	}

	function copy_files() {

		$nonce = filter_input( INPUT_GET, 'nonce' );

		if ( ! wp_verify_nonce( $nonce, 'embed_google_fonts_copy_files' ) ) {
			wp_die( __( 'Nonce check failed', 'embed-google-fonts' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_js( __( 'You are not allowed to copy files.', 'embed-google-fonts' ) ) );
		}

		$cacheFolder = apply_filters( 'embed_google_fonts_get_base_directory', false );
		$localFolder = apply_filters( 'embed_google_fonts_get_local_base_directory', false );

		if ( ! is_dir( $cacheFolder ) ) {
			wp_die( esc_js( __( 'No local cache found → nothing todo yet.', 'embed-google-fonts' ) ) );
		}

		if ( ! wp_mkdir_p( $localFolder ) ) {
			wp_die( esc_js( __( 'Error creating local path.', 'embed-google-fonts' ) ) );
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem( false, $cacheFolder );
		}

		$maybeError = copy_dir( $cacheFolder, $localFolder );

		if ( is_wp_error( $maybeError ) ) {
			wp_die( $maybeError->get_error_message() );
		}
		wp_die( esc_js( __( 'Cached files where copied.', 'embed-google-fonts' ) ) );
	}

	/**
	 * Add additional useful links.
	 *
	 * @param $links array Already existing links.
	 * @param $file string The current file.
	 *
	 * @return array Links including new ones.
	 */
	function init_row_meta( $links, $file ) {
		if ( strpos( $file, 'embed-google-fonts.php' ) === false ) {
			return $links;
		}
		ob_start();
		?>
		<section class="notice notice-info notice-info-embed-google-fonts"
		         style="display: block;padding: 10px; margin-top: 5px;">
			<p>
				<?php _e( 'Clicking the button will copy all cached font files to the local folder. No files will be removed, existing files will be overwritten.', 'embed-google-fonts' ) ?>
			</p>
			<button type="button" onclick="embed_google_fonts_copy_files()"
			        class="action-embed-google-fonts-copy"><?php _e( 'Copy cache to local', 'embed-google-fonts' ) ?></button>
			<script>
				<?php
				$confirmation = esc_js( __( 'Are you sure?', 'embed-google-fonts' ) );
				$url = add_query_arg(
					[
						'action' => 'embed_google_fonts_copy_files',
						'nonce'  => wp_create_nonce( 'embed_google_fonts_copy_files' )
					],
					admin_url( 'admin-ajax.php' ) );
				?>

				function embed_google_fonts_copy_files() {
					if (!confirm("<?= $confirmation ?>")) {
						return;
					}

					let url = "<?= $url ?>";

					// Making our request
					fetch(url, {method: 'GET'})
						.then(Result => Result.text())
						.then(message => {
							alert(message)
						})
						.catch(errorMsg => {
							alert(errorMsg)
						});
				}
			</script>
		</section>
		<?php
		$links[] = ob_get_clean();

		return $links;
	}
}
