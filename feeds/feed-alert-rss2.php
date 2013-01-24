<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */
 
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<?php do_action('rss2_head'); ?>
	
	<?php 
	$theme_options  = get_option(THEME_OPTIONS_NAME);
	$expiration		= $theme_options['outgoing_expiration'] ? (int)$theme_options['outgoing_expiration'] : 60;
	$alert_length	= $theme_options['outgoing_text_length'] ? (int)$theme_options['outgoing_text_length'] : 250;
	
	$args = array(
		'post_type'			=> 'alert',
		'posts_per_page'	=> 1,
		'orderby'			=> 'modified',
		'order'				=> 'DESC',
	);
	
	$feed_query = get_posts($args);
	$post		= $feed_query[0];
	
	if ( date('YmdHis', strtotime($post->post_modified)) >= date('YmdHis', strtotime('-'.$expiration.' minutes')) ) {
		$short = get_post_meta($post->ID, 'alert_short', True);
		if($short != '') {			
			// Truncate by text length, then remove the last full/partial word
			$short = substr($short, 0, $alert_length);
			$short = preg_replace('/ [^ ]*$/', ' ...', $short);
	?>
		<item>
			<title><?php the_title_rss() ?></title>
			<link><?php the_permalink_rss() ?></link>
			<postID><?php echo $post->ID; ?></postID>
			<alertType><?php $alert_type = get_post_meta($post->ID, 'alert_alert_type', True) ? get_post_meta($post->ID, 'alert_alert_type', True) : 'general'; echo $alert_type; ?></alertType>
			<comments><?php comments_link_feed(); ?></comments>
			<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
			<dc:creator><?php the_author() ?></dc:creator>
			<?php the_category_rss('rss2') ?>
	
			<guid isPermaLink="false"><?php the_guid(); ?></guid>
	<?php if (get_option('rss_use_excerpt')) : ?>
			<description><![CDATA[<?php echo $short; ?>]]></description>
	<?php else : ?>
			<description><![CDATA[<?php echo $short; ?>]]></description>
		<?php $content = get_the_content_feed('rss2'); ?>
		<?php if ( strlen( $content ) > 0 ) : ?>
			<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
		<?php else : ?>
			<content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded>
		<?php endif; ?>
	<?php endif; ?>
			<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
			<slash:comments><?php echo get_comments_number(); ?></slash:comments>
	<?php rss_enclosure(); ?>
		<?php do_action('rss2_item'); ?>
		</item>
	<?php 
		}
	}
	?>
</channel>
</rss>
