<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'ago_ai_settings' );
delete_option( 'ago_ai_files' );
delete_option( 'ago_ai_store_name' );
