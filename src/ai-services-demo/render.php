<?php
/**
 * Server-side render for the AI Services Demo chat block.
 *
 * When referenced via "render": "file:./render.php" in block.json, WordPress
 * will include this file and expect it to return the HTML string for the block.
 *
 * Available variables: $attributes, $content, $block
 *
 * @package ai-services-demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Generate a unique id to support multiple block instances on one page.
$instance_id = 'ai-services-demo-' . wp_generate_uuid4();
?>
<div class="wp-block-jonathanbossenger-ai-services-demo" data-instance="<?php echo esc_attr( $instance_id ); ?>">
    <div class="ai-chat" id="<?php echo esc_attr( $instance_id ); ?>">
        <div class="ai-chat__messages" aria-live="polite"></div>
        <form class="ai-chat__form" method="post" action="#" onsubmit="return false;">
            <label class="screen-reader-text"
                for="<?php echo esc_attr( $instance_id ); ?>-input"><?php esc_html_e( 'Message', 'ai-services-demo' ); ?></label>
            <textarea class="ai-chat__input" id="<?php echo esc_attr( $instance_id ); ?>-input" rows="3"
                placeholder="<?php echo esc_attr__( 'Type your message…', 'ai-services-demo' ); ?>"></textarea>
            <button type="submit" class="ai-chat__send"><?php echo esc_html__( 'Send', 'ai-services-demo' ); ?></button>
        </form>
        <noscript><?php echo esc_html__( 'JavaScript is required to use this chat.', 'ai-services-demo' ); ?></noscript>
    </div>
</div>