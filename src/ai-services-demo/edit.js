/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	const instanceId = 'ai-services-demo-editor';
	return (
		<div className="wp-block-jonathanbossenger-ai-services-demo" data-instance={instanceId}>
			<div className="ai-chat" id={instanceId}>
				<div className="ai-chat__messages" aria-live="polite"></div>
				<form className="ai-chat__form" method="post" action="#" onSubmit={e => e.preventDefault()}>
					<label className="screen-reader-text" htmlFor={`${instanceId}-input`}>
						{__('Message', 'ai-services-demo')}
					</label>
					<textarea
						className="ai-chat__input"
						id={`${instanceId}-input`}
						rows="3"
						placeholder={__('Type your message…', 'ai-services-demo')}
					></textarea>
					<button type="submit" className="ai-chat__send">
						{__('Send', 'ai-services-demo')}
					</button>
				</form>
				<noscript>{__('JavaScript is required to use this chat.', 'ai-services-demo')}</noscript>
			</div>
		</div>
	);
}
