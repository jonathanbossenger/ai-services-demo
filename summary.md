# Accessing AI Services in PHP (AI Services plugin) — Summary

This file summarizes how to use AI Services in PHP for the `ai-services-demo` plugin. Refer to the official guide: [Accessing AI Services in PHP](https://felixarntz.github.io/ai-services/Accessing-AI-Services-in-PHP.html).

## Entry point

- Use the global `ai_services()` function to access the `Services_API` singleton.
- Typical flow: get an available service → get a model → call a capability method.

## Finding an available AI service

- Specific provider by slug:
  - Prefer `ai_services()->is_service_available( 'google' )` then `get_available_service( 'google' )`.
  - Or wrap `get_available_service( 'slug' )` in `try { ... } catch ( InvalidArgumentException $e ) { ... }`.
- Any service by capability:
  - Use enums from `Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability`.
  - `has_available_services( [ 'capabilities' => [ AI_Capability::TEXT_GENERATION ] ] )` then `get_available_service( [ 'capabilities' => [ ... ] ] )`.
- Preferred list of slugs: pass `[ 'slugs' => [ 'google', 'openai' ] ]`; whichever is configured will be used.

## Getting a model

- Call `$service->get_model( $args )` with at minimum:
  - `feature` (string): unique identifier for your integration/feature.
- Optional args (normalized across providers when possible):
  - `capabilities`: e.g., `[ AI_Capability::TEXT_GENERATION ]`.
  - `model`: provider-specific model id (e.g., `gpt-4o`, `gemini-1.5-pro`).
  - `generationConfig`: typed config object (differs per capability, see below).
  - `systemInstruction`: system prompt to steer behavior.
  - `tools`: for function calling.
- Always wrap model calls in `try/catch ( Exception $e )`.

## Text generation

- Flow: get service (supports `TEXT_GENERATION`) → `get_model(...)` → `generate_text( $promptOrContent )`.
- Custom model: detect provider via `$service->get_service_slug()` and set a provider-specific `model`.
- Multimodal input: build `Content` with `Parts` (e.g., `add_text_part`, `add_file_data_part`) and require `MULTIMODAL_INPUT` + `TEXT_GENERATION`.
- Processing responses:
  - Methods return `Candidates`; each `Candidate` has `Content` with `Parts`.
  - Use helpers for robust text extraction: `Helpers::get_text_from_contents( Helpers::get_candidate_contents( $candidates ) )`.
- Streaming: `stream_generate_text()` returns a generator yielding partial `Candidates`.
- Configuration (`Text_Generation_Config`):
  - Common options: `stopSequences`, `maxOutputTokens`, `temperature`, `topP`, `topK` (not in `openai`).
  - Add `systemInstruction` in `get_model()` args to set a system prompt.
- Multimodal output (Google models as of Mar 2025):
  - Require `MULTIMODAL_OUTPUT` and set `generationConfig.outputModalities` with `Modality::TEXT`/`Modality::IMAGE`.

## Function calling

- Provide JSON Schema function declarations via `Tools->add_function_declarations_tool( $declarations )` and require `FUNCTION_CALLING` + `TEXT_GENERATION`.
- The model may return a `Function_Call_Part` alongside or instead of text.
- Extract `{ id, name, args }`, execute your function, then include a `function_response` via `Parts->add_function_response_part( $id, $name, $result )` in the next prompt (include prior history and the `tools` again).

## Image generation

- Capability: `IMAGE_GENERATION`; call `generate_image( $promptOrContent )`.
- Response processing:
  - `Inline_Data_Part` → base64 data URL; `File_Data_Part` → URL (often short TTL).
  - Convert/store via helpers and `Blob`:
    - `Helpers::base64_data_url_to_blob( $dataUrl )`
    - `Helpers::blob_to_base64_data_url( $blob )`
    - `Helpers::file_to_blob( $url, $mime = '' )`
    - `Helpers::file_to_base64_data_url( $url, $mime )`
- Configuration (`Image_Generation_Config`): common options include `candidateCount`, `aspectRatio`.

## Text to speech

- Capability: `TEXT_TO_SPEECH`; call `text_to_speech( $textOrContent )`.
- Response processing mirrors images (inline vs file data). Use the same helpers for audio blobs/data URLs.
- Configuration (`Text_To_Speech_Generation_Config`): common options include `voice`, `responseMimeType`.

## General practices

- Prefer capability-based selection over hardcoded providers.
- Always provide a stable `feature` id for attribution/observability.
- Wrap service/model calls in `try/catch` and provide UX fallbacks.
- Use enums (`AI_Capability`, `Modality`) and typed config classes for portability.
- Streaming helps UX most in JS; in PHP it’s still useful (e.g., WP-CLI).
- Current limits: built-in services don’t yet support multimodal input/history for image or TTS; check provider docs.

## Useful classes/namespaces (non-exhaustive)

- `Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability`, `Modality`, `Content_Role`.
- `Felix_Arntz\AI_Services\Services\API\Types\Content`, `Parts`, `Candidates`, `Candidate`, `Blob`.
- `Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config`, `Image_Generation_Config`, `Text_To_Speech_Generation_Config`.
- `Felix_Arntz\AI_Services\Services\API\Types\Tools` (function calling).
- `Felix_Arntz\AI_Services\Services\API\Helpers` (content/text extraction, file/blob utils).

## Links

- Plugin: [AI Services on WordPress.org](https://wordpress.org/plugins/ai-services/)
- GitHub: [felixarntz/ai-services](https://github.com/felixarntz/ai-services)
- Guide: [Accessing AI Services in PHP](https://felixarntz.github.io/ai-services/Accessing-AI-Services-in-PHP.html)

## Current plugin implementation: AI Services powered chat block

This plugin ships a dynamic block that renders a chat UI on the frontend and talks to the server via a REST endpoint which, in turn, uses the configured AI Services provider. No provider keys are exposed in the browser.

Key files

- PHP bootstrap and REST handler: `wp-content/plugins/ai-services-demo/ai-services-demo.php`
- Block metadata (dynamic): `src/ai-services-demo/block.json` with `"render": "file:./render.php"`
- Server-side render template (chat markup): `src/ai-services-demo/render.php`
- Frontend behavior (chat interactions): `src/ai-services-demo/view.js`
- Styles shared editor/frontend: `src/ai-services-demo/style.scss`

Block behavior

- Dynamic block: no saved content; rendered via `render.php`.
- Markup includes:
  - Wrapper: `.wp-block-jonathanbossenger-ai-services-demo`
  - Chat container: `.ai-chat` with a unique `id` and `data-instance`
  - Message list: `.ai-chat__messages`
  - Form: `.ai-chat__form` with textarea `.ai-chat__input` and button `.ai-chat__send`
- Styling provides bubble layout: `.ai-chat__message--user` (right/primary), `.ai-chat__message--assistant` (left/neutral).

Frontend logic (`view.js`)

- Initializes each `.ai-chat` instance on DOMContentLoaded.
- On submit:
  - Appends the user message to the transcript.
  - POSTs JSON to REST route with `{ message, instance }`.
  - Appends AI reply or an error fallback to the transcript.
- Expects globals (localized): `window.aiServicesDemo.restUrl` and `window.aiServicesDemo.nonce`.

REST API (`ai-services-demo.php`)

- Route: `POST /wp-json/ai-services-demo/v1/chat`
  - Body: `{ "message": string, "instance"?: string }`
  - Response: `{ "reply": string }` on success, or `{ "error": string, "message"?: string }` on failure.
- Permission: currently public (`__return_true`) to allow anonymous visitors to chat.

Server-side AI call (service-agnostic)

- Uses the AI Services API singleton via `ai_services()`.
- Selects any available service by capability:
  - Checks `has_available_services( [ 'capabilities' => [ AI_Capability::TEXT_GENERATION ] ] )`.
  - Fetches with `get_available_service( [ 'capabilities' => [ AI_Capability::TEXT_GENERATION ] ] )`.
- Gets a model:
  - `$model = $service->get_model( [ 'feature' => 'ai-services-demo/chat', 'capabilities' => [ AI_Capability::TEXT_GENERATION ] ] );`
  - Uses a stable `feature` id for attribution/observability.
- Generates a reply (single-turn):
  - `$candidates = $model->generate_text( $message );`
  - Extracts text via helpers: `Helpers::get_text_from_contents( Helpers::get_candidate_contents( $candidates ) )`.
- Errors: returns HTTP 400 for empty message, 503 if no service available, 500 for exceptions or if AI Services is unavailable.

Enqueuing and data

- The block’s `viewScript` in `block.json` ensures frontend JS is enqueued when the block appears.
- Additional localized data is provided to expose `restUrl` and a REST nonce via `wp_localize_script` (global `aiServicesDemo`).

Current limitations and next steps

- Single-turn only; no conversation history is persisted per instance.
- Public endpoint: consider adding rate limiting, anti-spam, abuse protections, and optional nonce enforcement.
- Optional enhancements:
  - Maintain per-session or per-instance chat history (transients/session/cookies) and use `Content` with prior turns.
  - Support streaming responses (`stream_generate_text`) for progressive rendering.
  - Add editor-side controls for system prompts, temperature/max tokens (`Text_Generation_Config`), and model selection.
  - Accessibility: add live-region improvements and keyboard handling refinements.
