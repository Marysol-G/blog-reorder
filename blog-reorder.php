<?php
/**
 * Plugin Name: Behavioral Recommendation Topics
 * Description: Displays a cookie-consent banner and reorders blog posts based on inferred user interests.
 * Version:     1.0.1
 * Author:      Marysol Gurrola
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue scripts and styles for consent banner and tracking
 */
function br_enqueue_assets() {
    // Minimal inline CSS for banner
    wp_add_inline_style( 'wp-block-library', "
        #br-consent-banner { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.85); color: #fff; padding: 1em; text-align: center; z-index: 9999; }
        #br-consent-banner button { margin-left: 0.5em; padding: 0.5em 1em; border: none; background: #4caf50; color: #fff; cursor: pointer; }
        #br-consent-banner button.decline { background: #f44336; }
    " );

    // Enqueue our consent/tracking script
    wp_enqueue_script(
        'br-consent-js',
        plugin_dir_url( __FILE__ ) . 'br-consent.js',
        [],
        '1.0.1',
        true
    );

    // Determine current topic slug from post tags
    $topics_of_interest = [ 'ai', 'marketing', 'engineering' ];
    $current_topic = '';
    if ( is_singular( 'post' ) ) {
        $tags = wp_get_post_tags( get_the_ID(), [ 'fields' => 'slugs' ] );
        if ( ! is_wp_error( $tags ) ) {
            foreach ( $topics_of_interest as $slug ) {
                if ( in_array( $slug, $tags, true ) ) {
                    $current_topic = $slug;
                    break;
                }
            }
        }
    }

    // Localize data for JS
    wp_localize_script( 'br-consent-js', 'brConsentData', [
        'cookieName'     => 'br_topic_weights',
        'consentName'    => 'br_personalization_consent',
        'cookieExpiry'   => DAY_IN_SECONDS * 30,
        'currentTopic'   => $current_topic,
    ] );
}
add_action( 'wp_enqueue_scripts', 'br_enqueue_assets' );

/**
 * Output cookie-consent banner markup
 */
function br_consent_banner_markup() {
    if ( isset( $_COOKIE['br_personalization_consent'] ) ) {
        return;
    }
    echo '<div id="br-consent-banner" role="alert">
            We use cookies to personalize posts based on your interests. 
            <button id="br-consent-accept">Accept</button>
            <button id="br-consent-decline" class="decline">Decline</button>
          </div>';
}
add_action( 'wp_footer', 'br_consent_banner_markup', 100 );

/**
 * Reorder main query posts based on topic weights cookie after consent
 */
function br_personalize_main_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( ! ( $query->is_home() || $query->is_post_type_archive( 'post' ) ) ) {
        return;
    }
    if ( empty( $_COOKIE['br_personalization_consent'] ) || '1' !== $_COOKIE['br_personalization_consent'] ) {
        return;
    }
    if ( empty( $_COOKIE['br_topic_weights'] ) ) {
        return;
    }
    $weights = json_decode( wp_unslash( $_COOKIE['br_topic_weights'] ), true );
    if ( ! is_array( $weights ) || empty( $weights ) ) {
        return;
    }
    arsort( $weights );
    $slugs = array_keys( $weights );

    add_filter( 'posts_clauses', function( $clauses ) use ( $slugs ) {
        global $wpdb;
        $case = 'CASE';
        $total = count( $slugs );
        foreach ( $slugs as $i => $slug ) {
            $rank = $total - $i;
            $case .= $wpdb->prepare( " WHEN t.slug = %s THEN %d", $slug, $rank );
        }
        $case .= ' ELSE 0 END';

        $clauses['join']  .= "
            LEFT JOIN {$wpdb->term_relationships} tr ON ({$wpdb->posts}.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'post_tag')
            LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
        ";
        $clauses['orderby'] = "({$case}) DESC, {$wpdb->posts}.post_date DESC";
        return $clauses;
    } );
}
add_action( 'pre_get_posts', 'br_personalize_main_query' );
