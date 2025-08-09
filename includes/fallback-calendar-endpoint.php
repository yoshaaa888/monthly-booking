<?php
/**
 * Fallback Calendar Endpoint
 * 
 * Provides a virtual endpoint for /monthly-calendar/ that doesn't depend
 * on a physical WordPress page existing. This ensures the calendar is
 * always accessible even if the page creation fails.
 */

class MB_Fallback_Calendar_Endpoint {
    
    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'handle_calendar_request' ) );
    }
    
    /**
     * Add rewrite rule for monthly-calendar endpoint
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^monthly-calendar/?$',
            'index.php?mb_calendar=1',
            'top'
        );
    }
    
    /**
     * Add custom query var
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'mb_calendar';
        return $vars;
    }
    
    /**
     * Handle calendar page requests
     */
    public function handle_calendar_request() {
        if ( get_query_var( 'mb_calendar' ) ) {
            $this->render_calendar_page();
            exit;
        }
    }
    
    /**
     * Render the calendar page
     */
    private function render_calendar_page() {
        status_header( 200 );
        header( 'Content-Type: text/html; charset=utf-8' );
        
        $site_title = get_bloginfo( 'name' );
        $site_url = home_url();
        
        $calendar_content = '';
        if ( function_exists( 'monthly_booking_calendar_shortcode' ) ) {
            $calendar_content = monthly_booking_calendar_shortcode( array() );
        } elseif ( shortcode_exists( 'monthly_calendar' ) ) {
            $calendar_content = do_shortcode( '[monthly_calendar]' );
        } elseif ( shortcode_exists( 'monthly_booking_calendar' ) ) {
            $calendar_content = do_shortcode( '[monthly_booking_calendar]' );
        } else {
            $calendar_content = '<div class="monthly-booking-fallback">
                <h2>Monthly Calendar</h2>
                <p>Calendar functionality is loading...</p>
                <div id="monthly-calendar-container"></div>
            </div>';
        }
        
        $html = '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Calendar - ' . esc_html( $site_title ) . '</title>
    ' . wp_head() . '
</head>
<body class="page page-monthly-calendar">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <article class="page type-page status-publish">
                <header class="entry-header">
                    <h1 class="entry-title">Monthly Calendar</h1>
                </header>
                <div class="entry-content">
                    ' . $calendar_content . '
                </div>
            </article>
        </main>
    </div>
    ' . wp_footer() . '
</body>
</html>';
        
        echo $html;
    }
    
    /**
     * Flush rewrite rules on activation
     */
    public static function flush_rules() {
        flush_rewrite_rules( true );
    }
}

new MB_Fallback_Calendar_Endpoint();

register_activation_hook( __FILE__, array( 'MB_Fallback_Calendar_Endpoint', 'flush_rules' ) );
