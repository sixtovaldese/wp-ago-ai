<?php
/**
 * Plugin Name:       aGo AI Chatbot, AI Chat Widget with Knowledge Base
 * Plugin URI:        https://ago.cl/herramientas/wordpress/ago-ai-chat/docs/
 * Description:       AI chat widget that answers visitor questions from your own knowledge files (PDF, TXT, CSV, MD, JSON), powered by Google Gemini. Fully functional, no built-in limits, no signup.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            aGo Lab
 * Author URI:        https://ago.cl
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ago-ai-chatbot
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

/* Coexistence with Pro: if Pro is active, Pro wins. Lite steps aside (skips
 * autoloader and hooks) so PHP classes are not redeclared, and shows an admin
 * notice telling the user the free version can be safely removed. */
$agoaichat_pro_active = in_array( 'ago-ai-pro/ago-ai-pro.php', (array) get_option( 'active_plugins', [] ), true );
if ( ! $agoaichat_pro_active && is_multisite() ) {
    $agoaichat_pro_active = array_key_exists( 'ago-ai-pro/ago-ai-pro.php', (array) get_site_option( 'active_sitewide_plugins', [] ) );
}
if ( $agoaichat_pro_active ) {
    add_action( 'admin_notices', function () {
        if ( ! current_user_can( 'activate_plugins' ) ) return;
        echo '<div class="notice notice-info is-dismissible"><p><strong>aGo AI Chatbot</strong>: ';
        echo esc_html__( 'aGo AI Chatbot Pro is active on this site and replaces this free version. You can safely deactivate and delete "aGo AI Chatbot" from the Plugins page. Your settings, files and conversations are preserved.', 'ago-ai-chatbot' );
        echo '</p></div>';
    });
    return;
}

defined( 'AGOAICHAT_VERSION' ) || define( 'AGOAICHAT_VERSION', '1.2.0' );
defined( 'AGOAICHAT_FILE' )    || define( 'AGOAICHAT_FILE', __FILE__ );
defined( 'AGOAICHAT_PATH' )    || define( 'AGOAICHAT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'AGOAICHAT_URL' )     || define( 'AGOAICHAT_URL', plugin_dir_url( __FILE__ ) );

/* PSR-4 Autoloader (path captured locally so Lite/Pro coexisting do not share state) */
$agoaichat_autoload_path = plugin_dir_path( __FILE__ );
spl_autoload_register( function ( string $class ) use ( $agoaichat_autoload_path ) {
    $prefix = 'AgoLab\\AIChatbot\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) return;
    $file = $agoaichat_autoload_path . 'src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
    if ( file_exists( $file ) ) require $file;
});

add_action( 'plugins_loaded', function () {
    \AgoLab\AIChatbot\Plugin::instance();
});
