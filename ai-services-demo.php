<?php
/**
 * Plugin Name:       Ai Services Demo
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-services-demo
 *
 * @package Jonathanbossenger
 */

define( 'PLUGIN_URL', trailingslashit( plugin_dir_url(__FILE__) ) );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function jonathanbossenger_ai_services_demo_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'jonathanbossenger_ai_services_demo_block_init' );

/**
 * Localize data for the frontend view script of our block.
 */
function jonathanbossenger_ai_services_demo_enqueue_data() {
	wp_enqueue_script(
		'ai-services-index',
		PLUGIN_URL . 'javascript/index.js',
		array( 'wp-api' ),
		'1.0.0',
		true
	);
	wp_localize_script(
		'ai-services-index',
		'aiServicesDemo',
		array(
			'restUrl' => rest_url( 'ai-services-demo/v1/chat' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);
	/*
	// Find the built view script handle from the asset file.
	$asset = __DIR__ . '/build/ai-services-demo/view.asset.php';
	if ( file_exists( $asset ) ) {
		$asset_data = include $asset;
		$handle    = 'jonathanbossenger-ai-services-demo-view';
		// wp_register_script( $handle, plugins_url( 'build/ai-services-demo/view.js', __FILE__ ), $asset_data['dependencies'] ?? array(), $asset_data['version'] ?? null, true );
		wp_localize_script(
			$handle,
			'aiServicesDemo',
			array(
				'restUrl' => rest_url( 'ai-services-demo/v1/chat' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
	*/
}
add_action( 'wp_enqueue_scripts', 'jonathanbossenger_ai_services_demo_enqueue_data' );

/**
 * Register REST API route for chat messages.
 */
function jonathanbossenger_ai_services_demo_register_rest_routes() {
	register_rest_route(
		'ai-services-demo/v1',
		'/chat',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'jonathanbossenger_ai_services_demo_handle_chat',
			'args'                => array(
				'message'  => array(
					'type'     => 'string',
					'required' => true,
				),
				'instance' => array(
					'type'     => 'string',
					'required' => false,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'jonathanbossenger_ai_services_demo_register_rest_routes' );

/**
 * Handle chat requests using the AI Services plugin, service-agnostic.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function jonathanbossenger_ai_services_demo_handle_chat( WP_REST_Request $request ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	$message = trim( (string) $request->get_param( 'message' ) );
	if ( '' === $message ) {
		return new WP_REST_Response( array( 'error' => 'empty_message' ), 400 );
	}

	// Ensure AI Services is available.
	if ( ! function_exists( 'ai_services' ) ) {
		return new WP_REST_Response( array( 'error' => 'ai_services_unavailable' ), 500 );
	}

	try {
		$services_api = ai_services();

		// Select any available service that supports text generation.
		if ( ! $services_api->has_available_services( array( 'capabilities' => array( \Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability::TEXT_GENERATION ) ) ) ) {
			return new WP_REST_Response( array( 'error' => 'no_service_available' ), 503 );
		}

		$service = $services_api->get_available_service( array( 'capabilities' => array( \Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability::TEXT_GENERATION ) ) );

		// Get a model for our feature.
		$model = $service->get_model( array(
			'feature'      => 'ai-services-demo-chat',
			'capabilities' => array( \Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability::TEXT_GENERATION ),
		) );

		// Simple single-turn chat: send user's message and get a reply.
		$candidates = call_user_func( array( $model, 'generate_text' ), $message );
		$contents   = \Felix_Arntz\AI_Services\Services\API\Helpers::get_candidate_contents( $candidates );
		$reply      = \Felix_Arntz\AI_Services\Services\API\Helpers::get_text_from_contents( $contents );

		return new WP_REST_Response( array( 'reply' => $reply ) );
	} catch ( Exception $e ) {
		return new WP_REST_Response( array( 'error' => 'exception', 'message' => $e->getMessage() ), 500 );
	}
}