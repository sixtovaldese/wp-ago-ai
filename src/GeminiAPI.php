<?php

namespace AgoLab\AIChatbot;

defined( 'ABSPATH' ) || exit;

class GeminiAPI {

    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Send a chat message using File Search (if store exists) or file_data fallback.
     */
    public static function chat( array $settings, string $message, array $history = [] ): array {
        $api_key = $settings['api_key'];
        $model   = $settings['model'] ?: 'gemini-2.5-flash-lite';

        // Build contents array with history
        $contents = [];
        foreach ( $history as $msg ) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [ 'role' => $role, 'parts' => [ [ 'text' => $msg['text'] ] ] ];
        }

        // Add current message
        $contents[] = [ 'role' => 'user', 'parts' => [ [ 'text' => $message ] ] ];

        // Build system instruction
        $system_text = self::build_system_prompt( $settings );
        $body = [ 'contents' => $contents ];
        if ( $system_text ) {
            $body['systemInstruction'] = [ 'parts' => [ [ 'text' => $system_text ] ] ];
        }

        $body['generationConfig'] = [
            'temperature'     => 0.7,
            'maxOutputTokens' => (int) ( $settings['max_output_tokens'] ?? 1000 ),
        ];

        // Use File Search Store if available (cheaper: only relevant fragments)
        $store_name = get_option( 'agoaichat_store_name', '' );
        if ( $store_name ) {
            $body['tools'] = [ [ 'fileSearch' => [ 'fileSearchStoreNames' => [ $store_name ] ] ] ];
        } else {
            // Fallback: attach files directly (sends entire file each time = expensive)
            $files = get_option( 'agoaichat_files', [] );
            if ( $files ) {
                $file_parts = [];
                foreach ( $files as $file ) {
                    if ( ! empty( $file['uri'] ) ) {
                        $file_parts[] = [ 'file_data' => [ 'mime_type' => $file['mime_type'], 'file_uri' => $file['uri'] ] ];
                    }
                }
                if ( $file_parts ) {
                    // Prepend file parts to the last user message
                    $last_idx = count( $contents ) - 1;
                    $contents[ $last_idx ]['parts'] = array_merge( $file_parts, $contents[ $last_idx ]['parts'] );
                    $body['contents'] = $contents;
                }
            }
        }

        $url = self::BASE_URL . "/models/{$model}:generateContent";

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json', 'x-goog-api-key' => $api_key ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'text' => '', 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            return [ 'text' => '', 'error' => $data['error']['message'] ?? 'API error (HTTP ' . $code . ')' ];
        }

        // Extract text from potentially multiple parts
        $text = '';
        foreach ( $data['candidates'][0]['content']['parts'] ?? [] as $part ) {
            if ( isset( $part['text'] ) ) {
                $text .= $part['text'];
            }
        }

        return [ 'text' => $text ];
    }

    /* ───── File Search Store Management ───── */

    /**
     * Create a File Search Store. Returns store name.
     */
    public static function create_store( string $api_key, string $display_name ): array {
        $response = wp_remote_post( self::BASE_URL . '/fileSearchStores', [
            'headers' => [ 'Content-Type' => 'application/json', 'x-goog-api-key' => $api_key ],
            'body'    => wp_json_encode( [ 'displayName' => $display_name ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'error' => $response->get_error_message() ];
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['name'] ) ) {
            return [ 'error' => $data['error']['message'] ?? 'Failed to create store' ];
        }

        return [ 'name' => $data['name'], 'displayName' => $data['displayName'] ?? '' ];
    }

    /**
     * Upload a file and add it to the File Search Store.
     */
    public static function upload_to_store( string $api_key, string $store_name, string $file_path, string $display_name, string $mime_type ): array {
        // Step 1: Upload file via Files API
        $upload_result = self::upload_file( $api_key, $file_path, $display_name, $mime_type );
        if ( ! empty( $upload_result['error'] ) ) {
            return $upload_result;
        }

        $file_name = $upload_result['name'];

        // Step 2: Add file to store
        $add_response = wp_remote_post( self::BASE_URL . '/' . $store_name . '/fileSearchStoreFiles', [
            'headers' => [ 'Content-Type' => 'application/json', 'x-goog-api-key' => $api_key ],
            'body'    => wp_json_encode( [ 'file' => $file_name ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $add_response ) ) {
            return [ 'error' => $add_response->get_error_message() ];
        }

        return [
            'name'     => $file_name,
            'uri'      => $upload_result['uri'],
            'in_store' => true,
        ];
    }

    /**
     * Delete a File Search Store.
     */
    public static function delete_store( string $api_key, string $store_name ): void {
        wp_remote_request( self::BASE_URL . '/' . $store_name . '?force=true', [
            'method'  => 'DELETE',
            'headers' => [ 'x-goog-api-key' => $api_key ],
            'timeout' => 15,
        ] );
    }

    /* ───── Files API (upload/delete) ───── */

    /**
     * Upload a file to Gemini Files API.
     */
    public static function upload_file( string $api_key, string $file_path, string $display_name, string $mime_type ): array {
        $file_size = filesize( $file_path );

        // Start resumable upload
        $start_url = 'https://generativelanguage.googleapis.com/upload/v1beta/files';

        $start_response = wp_remote_post( $start_url, [
            'headers' => [
                'x-goog-api-key'                     => $api_key,
                'X-Goog-Upload-Protocol'             => 'resumable',
                'X-Goog-Upload-Command'              => 'start',
                'X-Goog-Upload-Header-Content-Length' => $file_size,
                'X-Goog-Upload-Header-Content-Type'  => $mime_type,
                'Content-Type'                       => 'application/json',
            ],
            'body'    => wp_json_encode( [ 'file' => [ 'display_name' => $display_name ] ] ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $start_response ) ) {
            return [ 'error' => $start_response->get_error_message() ];
        }

        $headers    = wp_remote_retrieve_headers( $start_response );
        $upload_url = $headers['x-goog-upload-url'] ?? '';
        if ( ! $upload_url ) {
            return [ 'error' => 'Failed to get upload URL' ];
        }

        // Upload bytes
        $file_contents = file_get_contents( $file_path );
        $upload_response = wp_remote_request( $upload_url, [
            'method'  => 'PUT',
            'headers' => [
                'Content-Length'         => $file_size,
                'X-Goog-Upload-Offset'  => '0',
                'X-Goog-Upload-Command' => 'upload, finalize',
            ],
            'body'    => $file_contents,
            'timeout' => 60,
        ] );

        if ( is_wp_error( $upload_response ) ) {
            return [ 'error' => $upload_response->get_error_message() ];
        }

        $data = json_decode( wp_remote_retrieve_body( $upload_response ), true );
        if ( empty( $data['file']['uri'] ) ) {
            return [ 'error' => 'Upload failed: ' . wp_json_encode( $data ) ];
        }

        return [ 'name' => $data['file']['name'] ?? '', 'uri' => $data['file']['uri'] ];
    }

    /**
     * Delete a file from Gemini.
     */
    public static function delete_file( string $api_key, string $name ): void {
        wp_remote_request( self::BASE_URL . '/' . $name, [
            'method'  => 'DELETE',
            'headers' => [ 'x-goog-api-key' => $api_key ],
            'timeout' => 15,
        ] );
    }

    /* ───── Models ───── */

    /**
     * Cheap key check against the models endpoint before persisting it.
     */
    public static function validate_key( string $api_key ): array {
        $response = wp_remote_get( self::BASE_URL . '/models?pageSize=1', [
            'headers' => [ 'x-goog-api-key' => $api_key ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'ok' => false, 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            return [ 'ok' => false, 'error' => $data['error']['message'] ?? 'API error (HTTP ' . $code . ')' ];
        }

        return [ 'ok' => true ];
    }

    /**
     * Get available text chat models (Lite, Flash, Pro). Sorted cheapest tier
     * first, newest version first within each tier. Dynamic: any new Gemini
     * model the API exposes appears without code changes.
     */
    public static function list_models( string $api_key ): array {
        $response = wp_remote_get( self::BASE_URL . '/models?pageSize=200&key=' . $api_key, [ 'timeout' => 15 ] );
        if ( is_wp_error( $response ) ) return [];

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        $tiers = [
            'lite'  => [ 'order' => 1, 'tier' => 'Lowest cost' ],
            'flash' => [ 'order' => 2, 'tier' => 'Mid cost' ],
            'pro'   => [ 'order' => 3, 'tier' => 'Highest quality' ],
        ];

        $available = [];
        foreach ( $data['models'] ?? [] as $m ) {
            $name = str_replace( 'models/', '', $m['name'] );

            // Only Gemini text chat models. Non-conversational variants out.
            if ( ! str_starts_with( $name, 'gemini-' ) ) continue;
            if ( preg_match( '/(tts|image|audio|live|robotics|computer-use|embedding)/', $name ) ) continue;
            if ( ! in_array( 'generateContent', $m['supportedGenerationMethods'] ?? [], true ) ) continue;

            if ( str_contains( $name, 'lite' ) ) {
                $group = 'lite';
            } elseif ( str_contains( $name, 'flash' ) ) {
                $group = 'flash';
            } elseif ( str_contains( $name, 'pro' ) ) {
                $group = 'pro';
            } else {
                continue;
            }

            $label = ucwords( str_replace( '-', ' ', preg_replace( '/^gemini-/', 'Gemini ', $name ) ) );
            $available[] = [
                'id'    => $name,
                'label' => $label,
                'tier'  => $tiers[ $group ]['tier'],
                'order' => $tiers[ $group ]['order'],
            ];
        }

        usort( $available, fn( $a, $b ) => [ $a['order'], $b['id'] ] <=> [ $b['order'], $a['id'] ] );
        return $available;
    }

    /* ───── System Prompt ───── */

    private static function build_system_prompt( array $settings ): string {
        $site_name = get_bloginfo( 'name' );
        $parts = [];

        $bot_name = $settings['bot_name'] ?: 'Asistente';
        $parts[] = "You are {$bot_name}, the friendly assistant for \"{$site_name}\".";

        $style = $settings['response_style'] ?? 'friendly_emoji';
        $styles = [
            'friendly_emoji'  => "Respond in a warm, conversational, and human tone. Use emojis naturally (but not excessively) to make responses feel approachable 😊. Structure your answers with short paragraphs. Use bullet points only when listing more than 2 items. Avoid sounding robotic or overly formal.",
            'friendly_plain'  => "Respond in a warm, conversational, and human tone. Structure your answers with short paragraphs. Be concise and clear. Avoid sounding robotic.",
            'professional'    => "Respond in a professional but approachable tone. Be clear, structured, and concise. Use proper formatting with paragraphs and bullet points when appropriate.",
            'formal'          => "Respond in a formal, respectful tone. Use proper language and structured responses. Be thorough but concise.",
        ];
        $parts[] = $styles[ $style ] ?? $styles['friendly_emoji'];

        $parts[] = "IMPORTANT: You must ONLY answer questions related to the information in the knowledge files provided. If someone asks about unrelated topics (coding, math, general knowledge, etc.), politely redirect them: \"I'm here specifically to help you with information about {$site_name}. Is there something about our services I can help with? 😊\"";

        $tone = $settings['tone'] ?? 'friendly';
        if ( $tone === 'casual' ) {
            $parts[] = 'Be casual and relaxed, like talking to a friend. Use informal language.';
        }

        if ( ! empty( $settings['system_prompt'] ) ) {
            $parts[] = $settings['system_prompt'];
        }

        $parts[] = 'If a user asks to contact a human, suggest they use the contact options visible on this site (phone, email, contact form) and stay friendly.';

        $parts[] = 'If you cannot find the answer in your knowledge files, say so honestly and suggest they reach out through the site contact options. Never invent information.';

        $parts[] = 'Always respond in the same language the user writes in.';
        $parts[] = 'Keep responses concise, aim for 2-4 short paragraphs maximum unless the user asks for detail.';
        $parts[] = 'NEVER discuss your own errors, limitations, or technical issues. If the user mentions something "went wrong", apologize briefly and ask how you can help with ' . $site_name . ' services.';

        return implode( "\n\n", $parts );
    }
}
