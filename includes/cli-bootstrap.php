<?php
/**
 * WP-CLI Bootstrap Command for Monthly Booking Plugin
 * 
 * Provides `wp mb bootstrap` command to set up WordPress environment
 * for testing and CI environments.
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'mb', 'MB_CLI_Commands' );
}

class MB_CLI_Commands {
    
    /**
     * Bootstrap WordPress environment for Monthly Booking plugin
     * 
     * Sets up plugin activation, feature flags, permalinks, and calendar page
     * in a single idempotent command for CI/testing environments.
     * 
     * ## EXAMPLES
     * 
     *     wp mb bootstrap
     * 
     * @when after_wp_load
     */
    public function bootstrap( $args, $assoc_args ) {
        WP_CLI::log( "Starting Monthly Booking bootstrap process..." );
        
        try {
            $this->activate_plugin();
            
            $this->set_feature_flags();
            
            $this->configure_permalinks();
            
            $this->create_calendar_page();
            
            $this->health_check();
            
            WP_CLI::success( "Monthly Booking bootstrap completed successfully!" );
            
        } catch ( Exception $e ) {
            WP_CLI::error( "Bootstrap failed: " . $e->getMessage() );
        }
    }
    
    /**
     * Activate Monthly Booking plugin (idempotent)
     */
    private function activate_plugin() {
        WP_CLI::log( "== Activating Monthly Booking plugin ==" );
        
        $plugin_file = 'monthly-booking/monthly-booking.php';
        
        if ( ! is_plugin_active( $plugin_file ) ) {
            $result = activate_plugin( $plugin_file );
            if ( is_wp_error( $result ) ) {
                throw new Exception( "Failed to activate plugin: " . $result->get_error_message() );
            }
            WP_CLI::log( "Plugin activated successfully" );
        } else {
            WP_CLI::log( "Plugin already active" );
        }
        
        do_action( 'activate_' . $plugin_file );
    }
    
    /**
     * Set feature flags for test environment
     */
    private function set_feature_flags() {
        WP_CLI::log( "== Setting feature flags ==" );
        
        update_option( 'mb_feature_reservations_mvp', 1, true );
        WP_CLI::log( "Feature flag 'mb_feature_reservations_mvp' set to 1" );
        
        update_option( 'mb_bootstrap_completed', time(), true );
        WP_CLI::log( "Bootstrap timestamp recorded" );
    }
    
    /**
     * Configure permalink structure
     */
    private function configure_permalinks() {
        WP_CLI::log( "== Configuring permalinks ==" );
        
        $current_structure = get_option( 'permalink_structure' );
        $target_structure = '/%postname%/';
        
        if ( $current_structure !== $target_structure ) {
            update_option( 'permalink_structure', $target_structure );
            WP_CLI::log( "Permalink structure updated to: $target_structure" );
        } else {
            WP_CLI::log( "Permalink structure already correct: $target_structure" );
        }
        
        flush_rewrite_rules( true );
        WP_CLI::log( "Rewrite rules flushed" );
    }
    
    /**
     * Create monthly-calendar page if it doesn't exist
     */
    private function create_calendar_page() {
        WP_CLI::log( "== Creating calendar page ==" );
        
        $page = get_page_by_path( 'monthly-calendar' );
        
        if ( ! $page ) {
            $page_data = array(
                'post_type'     => 'page',
                'post_status'   => 'publish',
                'post_title'    => 'Monthly Calendar',
                'post_name'     => 'monthly-calendar',
                'post_content'  => '[monthly_calendar]',
                'post_author'   => 1
            );
            
            $page_id = wp_insert_post( $page_data );
            
            if ( is_wp_error( $page_id ) ) {
                throw new Exception( "Failed to create calendar page: " . $page_id->get_error_message() );
            }
            
            WP_CLI::log( "Calendar page created with ID: $page_id" );
        } else {
            WP_CLI::log( "Calendar page already exists: ID " . $page->ID );
        }
    }
    
    /**
     * Perform health check on calendar page
     */
    private function health_check() {
        WP_CLI::log( "== Health check ==" );
        
        $calendar_url = home_url( '/monthly-calendar/' );
        WP_CLI::log( "Checking URL: $calendar_url" );
        
        $response = wp_remote_head( $calendar_url, array(
            'timeout' => 10,
            'sslverify' => false
        ) );
        
        if ( is_wp_error( $response ) ) {
            WP_CLI::warning( "Health check failed: " . $response->get_error_message() );
            WP_CLI::log( "This may be normal in CLI context - check manually in browser" );
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
            WP_CLI::log( "HTTP Status: $status_code" );
            
            if ( $status_code === 200 ) {
                WP_CLI::success( "Health check passed!" );
            } else {
                WP_CLI::warning( "Unexpected status code: $status_code" );
            }
        }
    }
    
    /**
     * Show current bootstrap status
     * 
     * ## EXAMPLES
     * 
     *     wp mb status
     */
    public function status( $args, $assoc_args ) {
        WP_CLI::log( "=== Monthly Booking Status ===" );
        
        $plugin_active = is_plugin_active( 'monthly-booking/monthly-booking.php' );
        WP_CLI::log( "Plugin Active: " . ( $plugin_active ? 'Yes' : 'No' ) );
        
        $mvp_flag = get_option( 'mb_feature_reservations_mvp', 0 );
        WP_CLI::log( "MVP Feature Flag: $mvp_flag" );
        
        $permalink_structure = get_option( 'permalink_structure' );
        WP_CLI::log( "Permalink Structure: " . ( $permalink_structure ?: 'Plain' ) );
        
        $page = get_page_by_path( 'monthly-calendar' );
        WP_CLI::log( "Calendar Page: " . ( $page ? "Exists (ID: {$page->ID})" : 'Not found' ) );
        
        $bootstrap_time = get_option( 'mb_bootstrap_completed' );
        if ( $bootstrap_time ) {
            WP_CLI::log( "Last Bootstrap: " . date( 'Y-m-d H:i:s', $bootstrap_time ) . " UTC" );
        } else {
            WP_CLI::log( "Last Bootstrap: Never" );
        }
        
        $calendar_url = home_url( '/monthly-calendar/' );
        WP_CLI::log( "Calendar URL: $calendar_url" );
    }
}
