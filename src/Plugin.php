<?php

namespace AgoLab\AIChatbot;

defined( 'ABSPATH' ) || exit;

class Plugin {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'wp_footer', [ $this, 'render_widget' ] );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'ago-ai-chatbot', false, dirname( plugin_basename( AGOAICHAT_FILE ) ) . '/languages' );
    }

    /* ───── Admin Menu ───── */

    public function admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['agolab-tools'] ) ) {
            add_menu_page( __( 'aGo Tools', 'ago-ai-chatbot' ), __( 'aGo Tools', 'ago-ai-chatbot' ), 'manage_options', 'agolab-tools', '__return_null', 'dashicons-hammer', 81 );
        }

        add_submenu_page( 'agolab-tools', __( 'aGo AI Chatbot', 'ago-ai-chatbot' ), __( 'AI Chatbot', 'ago-ai-chatbot' ), 'manage_options', 'agoaichat', [ Admin\Settings::class, 'render' ] );

        remove_submenu_page( 'agolab-tools', 'agolab-tools' );
    }

    /* ───── Assets ───── */

    public function admin_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_agoaichat' ) ) return;
        wp_enqueue_media();
        wp_enqueue_style( 'agoaichat-admin', AGOAICHAT_URL . 'assets/css/admin.css', [], AGOAICHAT_VERSION );
        wp_enqueue_script( 'agoaichat-admin', AGOAICHAT_URL . 'assets/js/admin.js', [], AGOAICHAT_VERSION, true );
        wp_localize_script( 'agoaichat-admin', 'agoaichatAdmin', [
            'restUrl'  => rest_url( 'ago-ai-chatbot/v1' ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'settings' => self::get_settings(),
        ] );
    }

    public function frontend_assets(): void {
        $s = self::get_settings();
        if ( empty( $s['enabled'] ) || empty( $s['api_key'] ) ) return;

        wp_enqueue_style( 'agoaichat-widget', AGOAICHAT_URL . 'assets/css/widget.css', [], AGOAICHAT_VERSION );
        wp_enqueue_script( 'agoaichat-widget', AGOAICHAT_URL . 'assets/js/widget.js', [], AGOAICHAT_VERSION, true );
        wp_localize_script( 'agoaichat-widget', 'agoaichatWidget', [
            'restUrl'   => rest_url( 'ago-ai-chatbot/v1' ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'botName'   => $s['bot_name'] ?: __( 'Assistant', 'ago-ai-chatbot' ),
            'avatarUrl' => $s['avatar_url'] ?: AGOAICHAT_URL . 'assets/img/bot-avatar.svg',
            'welcomeMsg'=> $s['welcome_message'] ?: __( 'Hi! How can I help you?', 'ago-ai-chatbot' ),
            'maxIterations' => 20,
            'position'  => $s['widget_position'] ?? 'right',
            'offset'    => (int) ( $s['widget_offset'] ?? 0 ),
            'color'     => $s['widget_color'] ?? '#2271b1',
            'i18n'      => [
                'placeholder' => __( 'Type a message...', 'ago-ai-chatbot' ),
                'send'        => __( 'Send', 'ago-ai-chatbot' ),
                'thinking'    => __( 'Thinking...', 'ago-ai-chatbot' ),
                'error'       => __( 'Sorry, something went wrong. Please try again.', 'ago-ai-chatbot' ),
                'rateLimit'   => __( 'Too many messages in a short time. Please wait a moment and try again.', 'ago-ai-chatbot' ),
            ],
        ] );
    }

    /* ───── Widget ───── */

    public function render_widget(): void {
        $s = self::get_settings();
        if ( empty( $s['enabled'] ) || empty( $s['api_key'] ) ) return;
        echo '<div id="ago-ai-widget"></div>';
    }

    /* ───── REST API ───── */

    public function register_routes(): void {
        $ns = 'ago-ai-chatbot/v1';

        register_rest_route( $ns, '/settings', [
            [ 'methods' => 'GET', 'callback' => [ $this, 'rest_get_settings' ], 'permission_callback' => [ $this, 'can_manage' ] ],
            [ 'methods' => 'POST', 'callback' => [ $this, 'rest_save_settings' ], 'permission_callback' => [ $this, 'can_manage' ] ],
        ] );

        /* Public by design: the chat widget serves anonymous front-end visitors,
         * so this endpoint cannot require authentication. Access is gated inside
         * the callback (chat must be enabled) and throttled per IP. */
        register_rest_route( $ns, '/chat', [
            'methods' => 'POST', 'callback' => [ $this, 'rest_chat' ], 'permission_callback' => '__return_true',
        ] );

        register_rest_route( $ns, '/files/upload', [
            'methods' => 'POST', 'callback' => [ $this, 'rest_upload_file' ], 'permission_callback' => [ $this, 'can_manage' ],
        ] );

        register_rest_route( $ns, '/files', [
            'methods' => 'GET', 'callback' => [ $this, 'rest_list_files' ], 'permission_callback' => [ $this, 'can_manage' ],
        ] );

        register_rest_route( $ns, '/files/(?P<name>.+)', [
            'methods' => 'DELETE', 'callback' => [ $this, 'rest_delete_file' ], 'permission_callback' => [ $this, 'can_manage' ],
        ] );

        register_rest_route( $ns, '/models', [
            'methods' => 'GET', 'callback' => [ $this, 'rest_list_models' ], 'permission_callback' => [ $this, 'can_manage' ],
        ] );
    }

    public function can_manage(): bool { return current_user_can( 'manage_options' ); }

    /* ── REST: Settings ── */

    public function rest_get_settings(): \WP_REST_Response {
        $s = self::get_settings();
        $s['api_key'] = $s['api_key'] ? '********' : '';
        return new \WP_REST_Response( [ 'settings' => $s ] );
    }

    public function rest_save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();
        $settings = self::sanitize_settings( $data );
        // Don't overwrite API key if masked
        if ( $settings['api_key'] === '********' ) {
            $old = get_option( 'agoaichat_settings', [] );
            $settings['api_key'] = $old['api_key'] ?? '';
        } elseif ( $settings['api_key'] !== '' ) {
            // New key: verify against the API before persisting. A bad key
            // stored silently surfaces later as a generic chat failure.
            $check = GeminiAPI::validate_key( $settings['api_key'] );
            if ( empty( $check['ok'] ) ) {
                return new \WP_REST_Response( [ 'saved' => false, 'error' => 'Invalid API key: ' . ( $check['error'] ?? '' ) ], 400 );
            }
        }
        update_option( 'agoaichat_settings', $settings );
        $settings['api_key'] = $settings['api_key'] ? '********' : '';
        return new \WP_REST_Response( [ 'saved' => true, 'settings' => $settings ] );
    }

    /* ── REST: Chat ── */

    public function rest_chat( \WP_REST_Request $request ): \WP_REST_Response {
        $data     = $request->get_json_params();
        $settings = self::get_settings();

        // Gate: the widget must be enabled and configured before proxying any request.
        if ( empty( $settings['enabled'] ) ) {
            return new \WP_REST_Response( [ 'error' => 'Chat is not enabled' ], 403 );
        }

        if ( empty( $settings['api_key'] ) ) {
            return new \WP_REST_Response( [ 'error' => 'API key not configured' ], 500 );
        }

        // Anti-abuse rate limit per IP per minute (NOT a feature gating limit).
        // Plugin Lite has NO chat usage caps. This only prevents flood from one IP.
        $ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
        $rate_key = 'agoaichat_rate_' . md5( $ip );
        $count    = (int) get_transient( $rate_key );
        $limit    = max( 1, (int) ( $settings['rate_limit_per_minute'] ?? 60 ) );
        if ( $count >= $limit ) {
            return new \WP_REST_Response( [ 'error' => __( 'Too many messages in a short time. Please wait a moment and try again.', 'ago-ai-chatbot' ) ], 429 );
        }
        set_transient( $rate_key, $count + 1, MINUTE_IN_SECONDS );

        $message  = sanitize_textarea_field( $data['message'] ?? '' );
        $history  = $data['history'] ?? [];

        if ( ! $message ) {
            return new \WP_REST_Response( [ 'error' => 'Empty message' ], 400 );
        }

        $response = GeminiAPI::chat( $settings, $message, $history );

        return new \WP_REST_Response( [
            'text'  => $response['text'] ?? '',
            'error' => $response['error'] ?? null,
        ] );
    }

    /* ── REST: Files ── */

    public function rest_upload_file( \WP_REST_Request $request ): \WP_REST_Response {
        $settings = self::get_settings();

        if ( empty( $settings['api_key'] ) ) {
            return new \WP_REST_Response( [ 'error' => 'API key not configured' ], 500 );
        }

        $files = $request->get_file_params();
        if ( empty( $files['file'] ) ) {
            return new \WP_REST_Response( [ 'error' => 'No file provided' ], 400 );
        }

        $file = $files['file'];
        $allowed = [ 'application/pdf', 'text/plain', 'text/csv', 'text/markdown', 'application/json' ];
        if ( ! in_array( $file['type'], $allowed, true ) ) {
            return new \WP_REST_Response( [ 'error' => 'File type not allowed. Supported: PDF, TXT, CSV, MD, JSON' ], 400 );
        }

        // Ensure File Search Store exists
        $store_name = get_option( 'agoaichat_store_name', '' );
        if ( ! $store_name ) {
            $site_slug = sanitize_title( get_bloginfo( 'name' ) ) ?: 'ago-ai-chatbot';
            $store_result = GeminiAPI::create_store( $settings['api_key'], 'ago-ai-' . $site_slug );
            if ( ! empty( $store_result['error'] ) ) {
                return new \WP_REST_Response( [ 'error' => 'Store creation failed: ' . $store_result['error'] ], 500 );
            }
            $store_name = $store_result['name'];
            update_option( 'agoaichat_store_name', $store_name );
        }

        $result = GeminiAPI::upload_to_store( $settings['api_key'], $store_name, $file['tmp_name'], $file['name'], $file['type'] );

        if ( ! empty( $result['error'] ) ) {
            return new \WP_REST_Response( [ 'error' => $result['error'] ], 500 );
        }

        $saved_files = get_option( 'agoaichat_files', [] );
        $saved_files[] = [
            'name'         => $result['name'],
            'display_name' => $file['name'],
            'uri'          => $result['uri'],
            'mime_type'    => $file['type'],
            'size'         => $file['size'],
            'in_store'     => true,
            'uploaded_at'  => current_time( 'mysql' ),
        ];
        update_option( 'agoaichat_files', $saved_files );

        return new \WP_REST_Response( [ 'ok' => true, 'file' => end( $saved_files ), 'store' => $store_name ] );
    }

    public function rest_list_files(): \WP_REST_Response {
        return new \WP_REST_Response( [
            'files' => get_option( 'agoaichat_files', [] ),
            'store' => get_option( 'agoaichat_store_name', '' ),
        ] );
    }

    public function rest_delete_file( \WP_REST_Request $request ): \WP_REST_Response {
        $name     = $request['name'];
        $settings = self::get_settings();

        GeminiAPI::delete_file( $settings['api_key'], $name );

        $files = get_option( 'agoaichat_files', [] );
        $files = array_filter( $files, fn( $f ) => $f['name'] !== $name );
        update_option( 'agoaichat_files', array_values( $files ) );

        if ( empty( $files ) ) {
            $store_name = get_option( 'agoaichat_store_name', '' );
            if ( $store_name ) {
                GeminiAPI::delete_store( $settings['api_key'], $store_name );
                delete_option( 'agoaichat_store_name' );
            }
        }

        return new \WP_REST_Response( [ 'ok' => true ] );
    }

    public function rest_list_models(): \WP_REST_Response {
        $s = self::get_settings();
        if ( empty( $s['api_key'] ) ) return new \WP_REST_Response( [] );
        return new \WP_REST_Response( GeminiAPI::list_models( $s['api_key'] ) );
    }

    /* ───── Settings ───── */

    public static function get_settings(): array {
        $defaults = [
            'enabled'               => false,
            'api_key'               => '',
            'model'                 => 'gemini-2.0-flash-lite',
            'system_prompt'         => '',
            'tone'                  => 'friendly',
            'bot_name'              => '',
            'welcome_message'       => '',
            'avatar_url'            => '',
            'widget_position'       => 'right',
            'widget_offset'         => 0,
            'widget_color'          => '#2271b1',
            'rate_limit_per_minute' => 60,
            'max_input_tokens'      => 1000,
            'max_output_tokens'     => 1000,
            'response_style'        => 'friendly_emoji',
        ];
        return wp_parse_args( get_option( 'agoaichat_settings', [] ), $defaults );
    }

    private static function sanitize_settings( array $d ): array {
        return [
            'enabled'               => ! empty( $d['enabled'] ),
            'api_key'               => sanitize_text_field( $d['api_key'] ?? '' ),
            'model'                 => sanitize_text_field( $d['model'] ?? 'gemini-2.0-flash-lite' ),
            'system_prompt'         => sanitize_textarea_field( $d['system_prompt'] ?? '' ),
            'tone'                  => in_array( $d['tone'] ?? '', [ 'friendly', 'professional', 'casual', 'formal' ], true ) ? $d['tone'] : 'friendly',
            'bot_name'              => sanitize_text_field( $d['bot_name'] ?? '' ),
            'welcome_message'       => sanitize_text_field( $d['welcome_message'] ?? '' ),
            'avatar_url'            => esc_url_raw( $d['avatar_url'] ?? '' ),
            'widget_position'       => in_array( $d['widget_position'] ?? '', [ 'left', 'right' ], true ) ? $d['widget_position'] : 'right',
            'widget_offset'         => in_array( (int) ( $d['widget_offset'] ?? 0 ), [ 0, 1, 2 ], true ) ? (int) $d['widget_offset'] : 0,
            'widget_color'          => sanitize_hex_color( $d['widget_color'] ?? '#2271b1' ) ?: '#2271b1',
            'rate_limit_per_minute' => max( 10, min( 600, (int) ( $d['rate_limit_per_minute'] ?? 60 ) ) ),
            'max_input_tokens'      => max( 100, min( 10000, (int) ( $d['max_input_tokens'] ?? 1000 ) ) ),
            'max_output_tokens'     => max( 100, min( 10000, (int) ( $d['max_output_tokens'] ?? 1000 ) ) ),
            'response_style'        => in_array( $d['response_style'] ?? '', [ 'friendly_emoji', 'friendly_plain', 'professional', 'formal' ], true ) ? $d['response_style'] : 'friendly_emoji',
        ];
    }
}
