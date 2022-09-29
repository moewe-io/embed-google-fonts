<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 2.4.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Adrian Mörchen
 * Author URI: https://moerchen.io/
 * Text Domain: embed-google-fonts
 */

class Embed_Google_Fonts {

	function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'replace_queued_sources' ], PHP_INT_MAX );
		add_action( 'wp_print_styles', [ $this, 'replace_queued_sources' ], PHP_INT_MAX );

		add_action( 'wpfc_delete_cache', [ $this, 'clear_cache' ] );
		add_action( 'after_rocket_clean_domain', [ $this, 'clear_cache' ] );

		add_filter( 'embed_google_fonts_get_slug', [ $this, 'get_slug' ], 10, 1 );
		add_filter( 'embed_google_fonts_get_handle', [ $this, 'get_handle' ], 10, 1 );
		add_filter( 'embed_google_fonts_get_base_directory', [ $this, 'get_base_directory' ], 10, 1 );
		add_filter( 'embed_google_fonts_get_local_url', [ $this, 'get_local_url' ], 10, 2 );
	}

	function replace_queued_sources() {
		$wp_styles      = wp_styles();
		$base_url       = content_url( '/cache/embed-google-fonts/' );
		$base_directory = apply_filters( 'embed_google_fonts_get_base_directory', false );

		/** @var _WP_Dependency $dependency */
		foreach ( $wp_styles->registered as $key => $dependency ) {
			// Example https://fonts.googleapis.com/css?family=Lato:300
			if ( strpos( $dependency->src, 'fonts.googleapis.com/css' ) === false ) {
				continue;
			}
			$query    = wp_parse_url( $dependency->src, PHP_URL_QUERY );
			$query    = wp_parse_args( $query, array() );
			$families = explode( '|', $query['family'] );
			foreach ( $families as $family ) {
				if ( empty( $family ) || ! apply_filters( 'embed_google_fonts_download_font', true, $family ) ) {
					continue;
				}
				$family = explode( ':', $family )[0];
				$slug   = apply_filters( 'embed_google_fonts_get_slug', $family );
				$handle = apply_filters( 'embed_google_fonts_get_handle', $family );

				$version = 'local-' . date( 'Y-m' ); // Invalid at least monthly for local files
				$src_url = apply_filters( 'embed_google_fonts_get_local_url', false, $slug );
				if ( ! $src_url ) {
					$this->download_font( $base_directory, $slug );
					$version = is_file( $base_directory . $slug . '/font.css' ) ? filemtime( $base_directory . $slug . '/font.css' ) : time();
					$src_url = $base_url . $slug . '/font.css';
				}
				wp_enqueue_style( $handle, $src_url, false, $version );
			}
			// Remove original Google font from styles
			$wp_styles->remove( $key );
			$wp_styles->dequeue( $key );
		}
	}

	function get_handle( $name = '' ) {
		return 'embed-google-fonts-' . apply_filters( 'embed_google_fonts_get_slug', $name );
	}

	function get_slug( $name = '' ) {
		return strtolower( str_replace( ' ', '-', $name ) );
	}

	function get_local_url( $src, $slug ) {
		if ( $src ) {
			return $src;
		}

		$possibleCSS = WP_CONTENT_DIR . '/embed-google-fonts/' . $slug . '/font.css';

		if ( is_file( $possibleCSS ) ) {
			return content_url( '/embed-google-fonts/' . $slug . '/font.css' );
		}

		return $src;
	}

	private function download_font( $base_path, $slug ) {
		$directory                  = $base_path . $slug . '/';
		$css_file                   = $directory . 'font.css';
		$expiration_time_in_seconds = apply_filters( 'embed_google_fonts_expiration_time_in_seconds', MONTH_IN_SECONDS );
		$max_age                    = time() - $expiration_time_in_seconds;

		if ( is_file( $css_file ) && filemtime( $css_file ) > $max_age ) {
			return true;
		}

		/** Poor mans locking */
		if ( get_transient( 'embed-google-fonts-is-downloading-' . $slug ) ) {
			return true;
		}
		set_transient( 'embed-google-fonts-is-download-' . $slug, true, 30 );

		wp_delete_file( $css_file );

		$this->rrmdir( $directory );
		if ( ! wp_mkdir_p( $directory ) ) {
			error_log( 'Error creating needed directory: ' . $directory );

			return false;
		}
		$api_url = 'https://google-webfonts-helper.herokuapp.com/api/fonts/' . $slug;

		$subsets           = apply_filters( 'embed_google_fonts_get_subsets', [
			'devanagari',
			'vietnamese',
			'cyrillic-ext',
			'latin',
			'greek-ext',
			'greek',
			'cyrillic',
			'latin-ext',
			'hebrew',
			'oriya'
		] );
		$configuration_url = add_query_arg( [ 'subsets' => join( ',', $subsets ) ], $api_url );
		$response          = wp_remote_get( $configuration_url );

		if ( ! is_array( $response ) ) {
			/** @var WP_Error response */
			error_log( 'Error getting result: ' . $response->get_error_message() );

			return false;
		}
		$font_definition = json_decode( $response['body'] ); // use the content
		if ( $font_definition === null ) {
			error_log( 'Error getting font definition: ' . $slug );

			return false;
		}

		$download_url = add_query_arg( array(
			'download' => 'zip',
			'subsets'  => join( ",", $font_definition->subsets ),
		), $api_url );

		$download_target = $directory . 'font' . wp_generate_uuid4() . '.zip';

		// Download the fonts
		wp_remote_get( $download_url, array(
			'timeout'  => 300,
			'stream'   => true,
			'filename' => $download_target
		) );

		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem( false, $directory );
		$unzipfile = unzip_file( $download_target, $directory );
		unlink( $download_target );
		if ( is_wp_error( $unzipfile ) ) {
			/** @var WP_Error $unzipfile */
			error_log( "Error extracting font file: " . $slug . " -> " . $unzipfile->get_error_message() );

			return false;
		}

		ob_start();
		foreach ( $font_definition->variants as $variant ) {
			?>
			@font-face {
			font-family: <?= $variant->fontFamily ?>;
			font-style: <?= $variant->fontStyle ?>;
			font-weight: <?= $variant->fontWeight ?>;
			<?php
			$font_prefix = $slug . '-' . $font_definition->version . '-' . $font_definition->storeID . '-';
			if ( $variant->fontWeight == 400 ) {
				if ( $variant->fontStyle === 'italic' ) {
					$font_prefix .= 'italic';
				} else {
					$font_prefix .= 'regular';
				}
			} else {
				$font_prefix .= $variant->fontWeight . ( $variant->fontStyle === 'italic' ? 'italic' : '' );
			}

			if ( isset( $variant->eot ) ) {
				echo 'src: url("' . $font_prefix . '.eot"); /* IE9 Compat Modes */';
			}
			echo 'src:';

			if ( isset( $variant->local ) && is_array( $variant->local ) ) {
				foreach ( $variant->local as $local ) {
					echo 'local("' . $local . '"),';
				}
			}

			$formats = array();
			foreach (
				[
					'eot'      => '.eot?#iefix',
					'woff2'    => '.woff2',
					'woff'     => '.woff',
					'truetype' => '.truetype',
					'ttf'      => '.ttf',
					'svg'      => '.svg#' . $font_definition->family
				] as $format => $extension
			) {
				if ( isset( $variant->$format ) ) {
					$formats[] = 'url("' . $font_prefix . $extension . '") format("' . $format . '")';
				}
			}
			echo join( ',', $formats );
			?>
			;}
			<?php
		}

		$css_file_content = ob_get_clean();
		try {
			$file = fopen( $css_file, "w" );
			if ( flock( $file, LOCK_EX ) ) {
				fwrite( $file, $css_file_content );
			}
			if ( ! $file ) {
				error_log( "Error in opening new file: " . $css_file );

				return false;
			}
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );

			return false;
		} finally {
			fclose( $file );
		}

		return true;
	}

	function get_base_directory( $default = false ) {
		return WP_CONTENT_DIR . '/cache/embed-google-fonts/';
	}

	function clear_cache() {
		if ( apply_filters( 'embed_google_fonts_disable_clear_cache', false ) ) {
			return;
		}
		$directory = apply_filters( 'embed_google_fonts_get_base_directory', false );
		$this->rrmdir( $directory );
	}

	// Thanks: https://stackoverflow.com/a/3338133/1165132
	function rrmdir( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return;
		}
		$objects = scandir( $directory );
		foreach ( $objects as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( is_dir( $directory . DIRECTORY_SEPARATOR . $object ) && ! is_link( $directory . "/" . $object ) ) {
					$this->rrmdir( $directory . DIRECTORY_SEPARATOR . $object );
				} else {
					unlink( $directory . DIRECTORY_SEPARATOR . $object );
				}
			}
		}
		rmdir( $directory );
	}
}

new Embed_Google_Fonts();
