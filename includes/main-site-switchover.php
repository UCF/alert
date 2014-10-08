<?php
$theme_options = get_option(THEME_OPTIONS_NAME);
define(MAIN_SITE_ID, $theme_options['main_site_id']);
?>
<form method="post" id="theme-options" class="i-am-a-fancy-admin">
    <?php settings_fields(THEME_OPTIONS_GROUP);?>
	<div class="container">
		<h2>Emergency Main Site Switchover to Alerts Feed</h2>
		
		<?php if ($_POST['submit-button']) {
			switchout_main_site_homepg();
			print '<br/><br/><hr/><br/>';
		}
		?>
		
		<p>
			Click the button below to switch out the home page of ucf.edu to this site's front page.
		</p>

		<div class="submit">
			<input type="submit" class="button-primary" name="submit-button" value="<?= __('Switch')?>" />
		</div>
	</div>
</form>