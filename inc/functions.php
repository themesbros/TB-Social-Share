<?php
/**
 * Plugin functions.
 *
 * @package   TB Social Share
 * @version   1.0.0
 * @author    ThemesBros
 * @copyright Copyright (c) 2011 - 2017, ThemesBros
 */

/* If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function tb_social_share_get_excerpt( $text, $limit ) {
    if ( str_word_count( $text, 0 ) > $limit ) {
        $numwords = str_word_count( $text, 2 );
        $pos = array_keys( $numwords );
        $text = substr( $text, 0, $pos[$limit] );
    }
    return $text;
}

/**
 * Returns array of websites where articles will be shared.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function tb_social_share_get_sites() {

	$settings = get_option( 'tbss_settings' );

	if ( ! $settings['status'] ) {
		return;
	}

	global $post;
	$link        = get_permalink();
	$title       = get_the_title();
	$image       = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
	$image       = $image[0];
	$excerpt 	 = wp_trim_excerpt( get_post( $post->ID )->post_content );
	$description = tb_social_share_get_excerpt( $excerpt, 25 );

	$all_sites = array(

		'facebook'  => array(
						'class'     => 'facebook-bg',
						'share_url' => esc_url_raw( sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%s', $link ) ),
						'icon'      => 'fa-facebook',
						'title'     => 'Facebook',
					),
		'twitter'   => array(
						'class'     => 'twitter-bg',
						'share_url' => esc_url_raw( sprintf( 'https://twitter.com/share?text=%s&url=%s', $title, $link ) ),
						'icon'      => 'fa-twitter',
						'title'     => 'Twitter',
					),
		'gplus'     => array(
						'class'     => 'googleplus-bg',
						'share_url' => esc_url_raw( sprintf( 'https://plus.google.com/share?url=%s', $link ) ),
						'icon'      => 'fa-google-plus',
						'title'     => 'Google Plus',
					),
		'pinterest' => array(
						'class'     => 'pinterest-bg',
						'share_url' => esc_url_raw( sprintf( 'http://pinterest.com/pin/create/button/?url=%s&description=%s&media=%s', $link, $title, $image ) ),
						'icon'      => 'fa-pinterest-p',
						'title'     => 'Pinterest',
					),
		'linkedin'  => array(
						'class'     => 'linkedin-bg',
						'share_url' => esc_url_raw( sprintf( 'http://linkedin.com/shareArticle?mini=true&title=%s&url=%s', $title, $link ) ),
						'icon'      => 'fa-linkedin',
						'title'     => 'Linkedin',
					),
		'reddit'  => array(
						'class'     => 'reddit-bg',
						'share_url' => esc_url_raw( sprintf( 'http://reddit.com/submit?url=%s&title=%s', $link, $title ) ),
						'icon'      => 'fa-reddit-alien',
						'title'     => 'Reddit',
					),
		'tumblr'  => array(
						'class'     => 'tumblr-bg',
						'share_url' => esc_url_raw( sprintf( 'http://www.tumblr.com/share/link?url=%s&name=%s&description=%s', $link, $title, $description ) ),
						'icon'      => 'fa-tumblr',
						'title'     => 'Tumblr',
					),
		'vk'  	  => array(
						'class'     => 'vk-bg',
						'share_url' => esc_url_raw( sprintf( 'http://vk.com/share.php?url=%s&title=%s&description=%s', $link, $title, $description ) ),
						'icon'      => 'fa-vk',
						'title'     => 'VK',
					),
		'email'  => array(
						'class'     => 'email-bg',
						'share_url' => esc_url_raw( sprintf( 'mailto:?subject=%s&body=%s', $title, $link ) ),
						'icon'      => 'fa-envelope',
						'title'     => 'Email',
					),

	);

	$data          = array();
	$settings      = get_option( 'tbss_settings' );
	$enabled_sites = array_keys( $settings['site'], 1 );

	foreach( $enabled_sites as $site ) {
		$data[$site] = $all_sites[$site];
	}

	return $data;
}

/**
 * Displays social share sites.
 *
 * @since 1.0.0
 * @access public
 * @return string
 */
function tb_social_share_display() {

	$share_site = tb_social_share_get_sites();

	if ( ! $share_site ) {
		return;
	}

	$html = '<ul class="single-social-share">';

	foreach( $share_site as $site ) {
		$html .= '<li>';
			$html .= sprintf( '<a class="%s" href="%s"><i class="fa %s"></i> <span class="screen-reader-text">%s</span></a>',
						esc_attr( $site['class'] ),
						esc_url( $site['share_url'] ),
						esc_attr( $site['icon'] ),
						esc_html( $site['title'] )
					 );
		$html .= '</li>';
	}

	$html .= '</ul>';

	return apply_filters( 'tb_social_share_html', $html );
}

/* Adds social share to single post. */
add_filter( 'the_content', 'tb_social_share_add_to_post' );

/**
 * Add social share buttons to single post.
 *
 * @since 1.0.0
 * @access public
 * @param  string $content
 * @return string
 */
function tb_social_share_add_to_post( $content ) {

	if ( ! is_single() ) {
		return $content;
	}

	$settings = get_option( 'tbss_settings' );

	if ( 'custom' == $settings['position'] ) {
		return $content;
	}

	$html = tb_social_share_display();

	if ( 'before' == $settings['position'] ) {
		$content = $html . $content;
	} else {
		$content = $content . $html;
	}

	return $content;
}

/* Add meta tags to head. */
add_action( 'wp_head', 'tb_social_share_add_meta_tags');

/**
 * Adds Facebook meta tags to </head>.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function tb_social_share_add_meta_tags() {

	if ( ! is_single() ) {
		return;
	}

	$settings = get_option( 'tbss_settings' );

	if ( is_array( $settings['site'] ) && in_array( 'facebook', array_keys( $settings['site'], 1 ) ) ) {
		printf( '<meta property="og:site_name" content="%s" />%s', esc_attr( get_bloginfo( 'name' ) ), "\n" );
		printf( '<meta property="og:title" content="%s" />%s', esc_attr( get_the_title() ), "\n" );
		printf( '<meta property="og:url" content="%s" />%s', esc_url( get_permalink() ), "\n" );
		printf( '<meta property="og:image" content="%s" />%s', esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ), "\n" );
	}

}