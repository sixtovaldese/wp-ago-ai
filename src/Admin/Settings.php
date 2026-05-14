<?php

namespace AgoLab\AI\Admin;

use AgoLab\AI\Plugin;

defined( 'ABSPATH' ) || exit;

class Settings {

    public static function render(): void {
        $settings = Plugin::get_settings();
        $files = get_option( 'ago_ai_files', [] );
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( AGO_AI_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo AI', 'ago-ai-chatbot' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGO_AI_VERSION ); ?></span>
            </h1>

            <div class="ago-layout">
                <div class="ago-main">

                    <!-- General -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'General', 'ago-ai-chatbot' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Enable Chatbot', 'ago-ai-chatbot' ); ?></th>
                                <td><label><input type="checkbox" id="ago-enabled" <?php checked( ! empty( $settings['enabled'] ) ); ?>> <?php esc_html_e( 'Show AI chat widget on frontend', 'ago-ai-chatbot' ); ?></label></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Gemini API Key', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="password" id="ago-api-key" value="<?php echo esc_attr( $settings['api_key'] ? '********' : '' ); ?>" class="regular-text">
                                <p class="description"><a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener"><?php esc_html_e( 'Get your free API key', 'ago-ai-chatbot' ); ?></a></p></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Model', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <select id="ago-model">
                                        <option value="<?php echo esc_attr( $settings['model'] ); ?>"><?php echo esc_html( $settings['model'] ); ?> (<?php esc_html_e( 'current', 'ago-ai-chatbot' ); ?>)</option>
                                    </select>
                                    <button id="ago-refresh-models" class="button" style="margin-left:8px"><?php esc_html_e( 'Refresh Models', 'ago-ai-chatbot' ); ?></button>
                                    <span id="ago-models-status" style="margin-left:8px;font-size:12px;color:#666"></span>
                                    <p class="description"><?php esc_html_e( 'Flash Lite = cheapest, Flash = more capable. Verify costs at:', 'ago-ai-chatbot' ); ?> <a href="https://ai.google.dev/pricing" target="_blank" rel="noopener">ai.google.dev/pricing</a></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Personality -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Personality', 'ago-ai-chatbot' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Bot Name', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="text" id="ago-bot-name" value="<?php echo esc_attr( $settings['bot_name'] ); ?>" class="regular-text" placeholder="Assistant"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Welcome Message', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="text" id="ago-welcome-msg" value="<?php echo esc_attr( $settings['welcome_message'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Hi! How can I help you?', 'ago-ai-chatbot' ); ?>"></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Response Style', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <select id="ago-response-style">
                                        <option value="friendly_emoji" <?php selected( $settings['response_style'] ?? '', 'friendly_emoji' ); ?>><?php esc_html_e( 'Friendly with emojis (recommended)', 'ago-ai-chatbot' ); ?></option>
                                        <option value="friendly_plain" <?php selected( $settings['response_style'] ?? '', 'friendly_plain' ); ?>><?php esc_html_e( 'Friendly without emojis', 'ago-ai-chatbot' ); ?></option>
                                        <option value="professional" <?php selected( $settings['response_style'] ?? '', 'professional' ); ?>><?php esc_html_e( 'Professional', 'ago-ai-chatbot' ); ?></option>
                                        <option value="formal" <?php selected( $settings['response_style'] ?? '', 'formal' ); ?>><?php esc_html_e( 'Formal', 'ago-ai-chatbot' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Controls how the AI writes responses. Friendly with emojis feels the most human.', 'ago-ai-chatbot' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Tone', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <select id="ago-tone">
                                        <option value="friendly" <?php selected( $settings['tone'], 'friendly' ); ?>><?php esc_html_e( 'Friendly', 'ago-ai-chatbot' ); ?></option>
                                        <option value="professional" <?php selected( $settings['tone'], 'professional' ); ?>><?php esc_html_e( 'Professional', 'ago-ai-chatbot' ); ?></option>
                                        <option value="casual" <?php selected( $settings['tone'], 'casual' ); ?>><?php esc_html_e( 'Casual', 'ago-ai-chatbot' ); ?></option>
                                        <option value="formal" <?php selected( $settings['tone'], 'formal' ); ?>><?php esc_html_e( 'Formal', 'ago-ai-chatbot' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'System Prompt', 'ago-ai-chatbot' ); ?></th>
                                <td><textarea id="ago-system-prompt" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Custom instructions for the AI...', 'ago-ai-chatbot' ); ?>"><?php echo esc_textarea( $settings['system_prompt'] ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Tell the AI what it should know, how to behave, what topics to focus on, etc.', 'ago-ai-chatbot' ); ?></p></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Avatar', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <div style="display:flex;align-items:center;gap:12px">
                                        <img id="ago-avatar-preview" src="<?php echo esc_url( $settings['avatar_url'] ?: AGO_AI_URL . 'assets/img/bot-avatar.svg' ); ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #dcdcde">
                                        <div>
                                            <input type="hidden" id="ago-avatar-url" value="<?php echo esc_attr( $settings['avatar_url'] ); ?>">
                                            <button id="ago-avatar-upload" class="button"><?php esc_html_e( 'Upload Image', 'ago-ai-chatbot' ); ?></button>
                                            <button id="ago-avatar-remove" class="button" style="color:#d63638;<?php echo empty( $settings['avatar_url'] ) ? 'display:none' : ''; ?>"><?php esc_html_e( 'Remove', 'ago-ai-chatbot' ); ?></button>
                                            <p class="description"><?php esc_html_e( 'JPG, PNG or WebP. Max 1MB. Recommended: 96x96px.', 'ago-ai-chatbot' ); ?></p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Widget -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Widget Appearance', 'ago-ai-chatbot' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Position', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <select id="ago-widget-position">
                                        <option value="right" <?php selected( $settings['widget_position'], 'right' ); ?>><?php esc_html_e( 'Bottom Right', 'ago-ai-chatbot' ); ?></option>
                                        <option value="left" <?php selected( $settings['widget_position'], 'left' ); ?>><?php esc_html_e( 'Bottom Left', 'ago-ai-chatbot' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Altura Vertical', 'ago-ai-chatbot' ); ?></th>
                                <td>
                                    <select id="ago-widget-offset">
                                        <option value="0" <?php selected( (int) ( $settings['widget_offset'] ?? 0 ), 0 ); ?>><?php esc_html_e( 'Estandar (default)', 'ago-ai-chatbot' ); ?></option>
                                        <option value="1" <?php selected( (int) ( $settings['widget_offset'] ?? 0 ), 1 ); ?>><?php esc_html_e( 'Un nivel arriba (+80px)', 'ago-ai-chatbot' ); ?></option>
                                        <option value="2" <?php selected( (int) ( $settings['widget_offset'] ?? 0 ), 2 ); ?>><?php esc_html_e( 'Dos niveles arriba (+160px)', 'ago-ai-chatbot' ); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Sube el widget verticalmente para no chocar con otros botones flotantes (ej. WhatsApp).', 'ago-ai-chatbot' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Color', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="color" id="ago-widget-color" value="<?php echo esc_attr( $settings['widget_color'] ); ?>"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Knowledge Base -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Knowledge Base', 'ago-ai-chatbot' ); ?></h2>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'Upload files (PDF, TXT, CSV, MD, JSON) for the AI to use as reference. Files are processed by Gemini.', 'ago-ai-chatbot' ); ?></p>
                        <div style="margin-bottom:12px">
                            <input type="file" id="ago-file-input" accept=".pdf,.txt,.csv,.md,.json,.json5">
                            <button id="ago-upload-file" class="button"><?php esc_html_e( 'Upload', 'ago-ai-chatbot' ); ?></button>
                            <span id="ago-upload-status" style="margin-left:8px;font-size:13px"></span>
                        </div>
                        <table class="wp-list-table widefat striped" id="ago-files-table" <?php echo empty( $files ) ? 'style="display:none"' : ''; ?>>
                            <thead><tr><th><?php esc_html_e( 'File', 'ago-ai-chatbot' ); ?></th><th><?php esc_html_e( 'Type', 'ago-ai-chatbot' ); ?></th><th><?php esc_html_e( 'Date', 'ago-ai-chatbot' ); ?></th><th><?php esc_html_e( 'Actions', 'ago-ai-chatbot' ); ?></th></tr></thead>
                            <tbody id="ago-files-tbody">
                                <?php foreach ( $files as $f ) : ?>
                                <tr data-name="<?php echo esc_attr( $f['name'] ); ?>">
                                    <td><?php echo esc_html( $f['display_name'] ); ?></td>
                                    <td><?php echo esc_html( $f['mime_type'] ); ?></td>
                                    <td><?php echo esc_html( $f['uploaded_at'] ?? '' ); ?></td>
                                    <td><button class="button ago-delete-file" data-name="<?php echo esc_attr( $f['name'] ); ?>"><?php esc_html_e( 'Delete', 'ago-ai-chatbot' ); ?></button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p id="ago-no-files" <?php echo ! empty( $files ) ? 'style="display:none"' : ''; ?> style="color:#666;font-size:13px"><?php esc_html_e( 'No files uploaded yet.', 'ago-ai-chatbot' ); ?></p>
                    </div>

                    <!-- Limits -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Limits & Notifications', 'ago-ai-chatbot' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Max Input Tokens', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="number" id="ago-max-input-tokens" value="<?php echo (int) ($settings['max_input_tokens'] ?? 1000); ?>" min="100" max="10000" style="width:80px"> <span class="description"><?php esc_html_e( 'per message (default: 1000)', 'ago-ai-chatbot' ); ?></span></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Max Output Tokens', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="number" id="ago-max-output-tokens" value="<?php echo (int) ($settings['max_output_tokens'] ?? 1000); ?>" min="100" max="10000" style="width:80px"> <span class="description"><?php esc_html_e( 'per response (default: 1000)', 'ago-ai-chatbot' ); ?></span></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Rate Limit', 'ago-ai-chatbot' ); ?></th>
                                <td><input type="number" id="ago-rate-limit" value="<?php echo (int) $settings['rate_limit']; ?>" min="1" max="500" style="width:70px"> <span class="description"><?php esc_html_e( 'messages per hour per IP', 'ago-ai-chatbot' ); ?></span></td>
                            </tr>
                        </table>
                    </div>

                    <button id="ago-save-settings" class="button button-primary button-hero"><?php esc_html_e( 'Save Settings', 'ago-ai-chatbot' ); ?></button>
                    <div id="ago-status" style="display:none"></div>

                </div>

                <!-- SIDEBAR -->
                <div class="ago-sidebar">
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-ai-chatbot' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'AI chatbot powered by Google Gemini. Upload knowledge files, customize the personality, and chat with your visitors. Free and fully functional, no built-in limits.', 'ago-ai-chatbot' ); ?></p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( 'Google Gemini AI', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Unlimited knowledge files (PDF, TXT, CSV, MD, JSON)', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Customizable personality and tone', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Configurable widget (position, color, avatar)', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Anti-abuse rate limit (configurable)', 'ago-ai-chatbot' ); ?></li>
                        </ul>
                    </div>
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'Additional features available', 'ago-ai-chatbot' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'A separate companion product, distributed outside WordPress.org, adds capabilities that are not part of this plugin:', 'ago-ai-chatbot' ); ?></p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( 'Conversation history and search', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Lead capture form inside chat', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Task engine (bot creates tasks)', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Multi-provider with failover (OpenAI, Anthropic, Groq)', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Analytics dashboard', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Voice input and synthesis', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'Handoff to human (Email, Slack, Telegram)', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'WooCommerce product knowledge', 'ago-ai-chatbot' ); ?></li>
                            <li><?php esc_html_e( 'White-label widget', 'ago-ai-chatbot' ); ?></li>
                        </ul>
                        <a href="https://ago.cl/herramientas/wordpress/ago-ai-chat/docs/" target="_blank" rel="noopener" class="button" style="margin-top:6px"><?php esc_html_e( 'Learn more', 'ago-ai-chatbot' ); ?></a>
                    </div>
                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support Open Source', 'ago-ai-chatbot' ); ?></h3>
                        <p style="font-size:13px;color:#666"><?php esc_html_e( 'If this plugin saves you time, consider supporting our open-source work.', 'ago-ai-chatbot' ); ?></p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-coffee" style="margin-right:6px"></span>
                            <?php esc_html_e( 'Buy us a coffee', 'ago-ai-chatbot' ); ?>
                        </a>
                        <p class="ago-donation-note"><?php esc_html_e( 'Voluntary donation. Thank you!', 'ago-ai-chatbot' ); ?></p>
                    </div>
                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo">
                            <img src="<?php echo esc_url( AGO_AI_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto">
                        </a>
                        <p><?php echo wp_kses_post( sprintf(
                            /* translators: 1: heart icon HTML, 2: aGo Lab link HTML */
                            __( 'Developed with %1$s by %2$s', 'ago-ai-chatbot' ),
                            '<span style="color:#e25555">&#10084;</span>',
                            '<a href="https://ago.cl" target="_blank" rel="noopener"><strong>aGo Lab</strong></a>'
                        ) ); ?></p>
                        <p style="font-size:11px;color:#999"><?php esc_html_e( 'Building tools for the web, one plugin at a time.', 'ago-ai-chatbot' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
