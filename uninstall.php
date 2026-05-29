<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'agoaichat_settings' );
delete_option( 'agoaichat_files' );
delete_option( 'agoaichat_store_name' );
