<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php echo "\n".header_()."\n"; ?>

		<?php if ( GA_ACCOUNT || CB_UID ) : ?>

		<script type="text/javascript">
			var _sf_startpt = (new Date()).getTime();
			<?php if ( GA_ACCOUNT ) : ?>

			var GA_ACCOUNT  = '<?php echo GA_ACCOUNT; ?>';
			var _gaq        = _gaq || [];
			_gaq.push(['_setAccount', GA_ACCOUNT]);
			_gaq.push(['_setDomainName', 'none']);
			_gaq.push(['_setAllowLinker', true]);
			_gaq.push(['_trackPageview']);
			<?php endif; ?>
			<?php if ( CB_UID ) : ?>

			var CB_UID      = '<?=CB_UID?>';
			var CB_DOMAIN   = '<?=CB_DOMAIN?>';
			<?php endif; ?>

		</script>
		<?php endif;?>

		<?php
		if ( isset( $post ) && $post instanceof WP_Post ):
			$post_type = get_post_type( $post->ID );
			if ( ( $stylesheet_id = get_post_meta( $post->ID, $post_type.'_stylesheet', true ) ) !== false
				&& ( $stylesheet_url = wp_get_attachment_url( $stylesheet_id ) ) !== false ) :
		?>
				<link rel='stylesheet' href="<?php echo $stylesheet_url; ?>" type='text/css' media='all' />
		<?php
			endif;
		endif;
		?>

	</head>
	<body>
		<div class="container">
			<div class="row"  id="header">
				<h1 class="span10"><a href="<?php echo bloginfo('url'); ?>">UCF Alert</a></h1>
			</div>
