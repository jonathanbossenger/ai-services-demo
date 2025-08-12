<?php
add_action( 'wp_enqueue_scripts', 'jonathanbossenger_ai_services_demo_enqueue_data' );
/**
 * Localize data for the frontend view script of our block.
 */
function jonathanbossenger_ai_services_demo_enqueue_data() {
	wp_localize_script(
		'jonathanbossenger-ai-services-demo-view-script',
		'aiServicesDemo',
		array(
			'restUrl' => rest_url( 'ai-services-demo/v1/chat' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);
}
