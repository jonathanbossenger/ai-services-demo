<?php

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Helpers;

add_action( 'rest_api_init', 'jonathanbossenger_ai_services_demo_register_rest_routes' );
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
		if ( ! $services_api->has_available_services( array( 'capabilities' => array( AI_Capability::TEXT_GENERATION ) ) ) ) {
			return new WP_REST_Response( array( 'error' => 'no_service_available' ), 503 );
		}

		$service = $services_api->get_available_service( array( 'capabilities' => array( AI_Capability::TEXT_GENERATION ) ) );

		// Get a model for our feature.
		$model = $service->get_model( array(
			'feature'      => 'ai-services-demo-chat',
			'capabilities' => array( AI_Capability::TEXT_GENERATION ),
		) );

		// Simple single-turn chat: send user's message and get a reply.
		$candidates = call_user_func( array( $model, 'generate_text' ), $message );
		$contents   = Helpers::get_candidate_contents( $candidates );
		$reply      = Helpers::get_text_from_contents( $contents );

		return new WP_REST_Response( array( 'reply' => $reply ) );
	} catch ( Exception $e ) {
		return new WP_REST_Response( array( 'error' => 'exception', 'message' => $e->getMessage() ), 500 );
	}
}
