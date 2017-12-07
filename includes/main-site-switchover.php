<?php
$theme_options = get_option(THEME_OPTIONS_NAME);
define(MAIN_SITE_ID, $theme_options['main_site_id']);
?>
<form method="post" id="main-site-switchover">
    <?php settings_fields(THEME_OPTIONS_GROUP);?>
	<div class="container">
		<h2>Emergency Text-Only Main Site Switchover</h2>

		<?php
		// Check for any possible configuration errors with the
		// Main Site before allowing the user to perform activation/
		// deactivation actions.
		$errors = pre_main_site_switchover_errors();

		if (!$errors) {
			if ($_POST['submit-button']) {
				if ($_POST['submit-button'] == 'Activate') {
					$switch = switchout_main_site_homepg();
					if ($switch) {
						print '<div id="message" class="updated"><p>Successfully activated text-only switchover.</p></div>';
					}
					else {
						print '<div id="message" class="error"><p><strong>Error: Failed to activate text-only switchover!</strong></p><ul>';
						foreach ($switch->get_error_messages() as $switch_error) {
							print '<li>'.$switch_error.'</li>';
						}
						print '</ul></div>';
					}
				}
				else {
					$switch = switchout_main_site_homepg(false);
					if ($switch) {
						print '<div id="message" class="updated"><p>Successfully deactivated text-only switchover.</p></div>';
					}
					else {
						print '<div id="message" class="error"><p><strong>Error: Failed to deactivate text-only switchover!</strong></p><ul>';
						foreach ($switch->get_error_messages() as $switch_error) {
							print '<li>'.$switch_error.'</li>';
						}
						print '</ul></div>';
					}
				}
			}

			$homepg_is_switched = is_main_site_homepg_switched();
			?>

			<div class="well">
				<p>Current status: <?=$homepg_is_switched ? '<span class="activated">Activated</span>' : '<span class="deactivated">Deactivated</span>'?></p>
			</div>

			<p>
				Click the button below to toggle the home page of ucf.edu.  When activated, the home page of
				www.ucf.edu will redirect to www.ucf.edu/alert.  Deactivating this switch will return ucf.edu
				to its normal home page.
			</p>
			<?php
			$do_ban = filter_var( $theme_options['main_site_homepg_switchout_ban'], FILTER_VALIDATE_BOOLEAN );
			if ( $do_ban ) :
			?>
			<p>
				Any time this toggle is switched, the home page cache of ucf.edu is removed (banned) to prevent
				stale content from displaying and to ensure the switchover takes effect immediately.
			</p>
			<?php endif; ?>
			<p>
				This action will take effect immediately.  <strong>Do not click the button below unless there
				is an actual emergency, or you are performing official university emergency response tests.</strong>
			</p>

			<div class="submit">
				<?php if ($homepg_is_switched) { ?>
					<input type="submit" class="button" name="submit-button" value="<?= __('Deactivate')?>" />
				<?php } else { ?>
					<input type="submit" class="button-primary" name="submit-button" value="<?= __('Activate')?>" onclick="return confirm('Are you sure you want to change the homepage of ucf.edu to display alert content?')" />
				<?php } ?>
			</div>

			<h3>Logs</h3>
			<?php
			$logs = get_option('main_site_switchover_logs');
			if (is_array($logs) && !empty($logs)) { ?>
				<ul>
				<?php
				foreach ($logs as $log) {
					$date = strtotime($log->date);
					$date_est = new DateTime(null, new DateTimeZone('America/New_York'));
					$date_est->setTimestamp($date);
				?>
					<li><?=$date_est->format('Y/m/d g:i A')?> EST: <strong><?=get_user_by('id', $log->user)->get('user_login');?></strong> <?=$log->activated == true ? 'activated' : 'deactivated'?> switchover</li>
				<?php } ?>
				</ul>
			<?php
			} else { ?>
			<p><em>No logs available.</em></p>
			<?php
			}
		}
		else {
			print '<div id="message" class="error"><p><strong>Error: The Emergency Text-Only switchover cannot be performed until the following errors are fixed:</strong></p><ul>';
			foreach ($errors->get_error_messages() as $error) {
				print '<li>'.$error.'</li>';
			}
			print '</ul></div>';
		}
		?>
	</div>
</form>
