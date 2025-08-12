/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/* eslint-disable no-console */
console.log('Hello World! (from jonathanbossenger-ai-services-demo block)');
/* eslint-enable no-console' */

/**
 * Frontend logic for AI Services Demo chat block.
 */

const initChat = (root) => {
    const messagesEl = root.querySelector('.ai-chat__messages');
    const form = root.querySelector('.ai-chat__form');
    const input = root.querySelector('.ai-chat__input');
    const loadingEl = root.querySelector('.ai-chat__loading');

    if (!messagesEl || !form || !input) return;

    // Move loading animation above textarea and button
    if (loadingEl && form.parentNode !== null) {
        form.parentNode.insertBefore(loadingEl, form);
    }

    const appendMsg = (role, text) => {
        if (loadingEl) loadingEl.style.display = 'none';
        const msg = document.createElement('div');
        msg.className = `ai-chat__message ai-chat__message--${role}`;
        msg.textContent = text;
        messagesEl.appendChild(msg);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    const setBusy = (busy) => {
        form.querySelector('button[type="submit"]').disabled = !!busy;
        input.disabled = !!busy;
        if (loadingEl) loadingEl.style.display = busy ? '' : 'none';
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        appendMsg('user', text);
        input.value = '';
        setBusy(true);
        console.log('Nonce', window.aiServicesDemo?.nonce || 'empty nonce');
        try {
            const res = await fetch(window.aiServicesDemo?.restUrl || '/wp-json/ai-services-demo/v1/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.aiServicesDemo?.nonce || ''
                },
                body: JSON.stringify({ message: text, instance: root.dataset.instance || '' })
            });
            if (!res.ok) throw new Error('Request failed');
            const data = await res.json();
            appendMsg('assistant', data?.reply || '');
        } catch (err) {
            console.error('Error fetching AI response:', err);
            appendMsg('assistant', 'Sorry, something went wrong.');
        } finally {
            setBusy(false);
        }
    });
};

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wp-block-jonathanbossenger-ai-services-demo .ai-chat').forEach(initChat);
});
