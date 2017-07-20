<?php
global $post;
$form_options = get_option('wtrc_form_settings');
$permissions = get_option('wtrc_permissions_settings');
$required_fields = $form_options['required_fields'];

?>
<div class="wtrc-container <?php echo $form_options['color_scheme']; ?>">
	<button type="button" class="wtrc-switch"><?php echo $form_options['slidedown_button_text']; ?></button>
	<div class="wtrc-content">
		<div class="wtrc-message">
		</div>
		<div class="wtrc-form">
			<?php if ($permissions['login_required'] && !is_user_logged_in()): ?>
				To remind this post you need to <a href="<?php echo wp_login_url(); ?>" title="Login">login</a> first.
			<?php else: ?>
			<div class="left-section">
				<li class="list-item-name">
					<?php if ($form_options['active_fields']['reminder_name']): ?>
						<input type="text" placeholder="Name" id="input-name-<?php echo $post->ID; ?>" class="input-name wtrc-input" <?php if ($required_fields['reminder_name']): ?> required <?php endif; ?> /> <br>
					<?php endif; ?>
				</li>
				<li class="list-item-email">
						<input type="text" placeholder="Email" id="input-email-<?php echo $post->ID; ?>" class="input-email wtrc-input" required  /> <br>
				</li>
				<li class="list-item-date">
						<input type="text" Placeholder="Date" id="input-date-<?php echo $post->ID; ?>" class="wtrc-datepicker input-date wtrc-input" required />   <br>
				</li>
				<li class="list-item-time">
						<input type="text" placeholder="Time" id="input-time-<?php echo $post->ID; ?>" class="wtrc-timepicker input-time wtrc-input" required />  
				</li>
			</div>
			<div class="right-section">
				<li class="list-item-details">
					<?php if ($form_options['active_fields']['comment']): ?>
						<textarea id="input-details-<?php echo $post->ID; ?>" placeholder="Comment" class="input-details wtrc-input" <?php if ($required_fields['comment']): ?> required <?php endif; ?>></textarea>
					<?php endif; ?>
				</li>
			</div>
			<div class="clear"></div>
			<input type="hidden" class="post-id" value="<?php echo $post->ID; ?>">
			<button type="button" class="wtrc-submit"><?php echo $form_options['submit_button_text'] ?></button>
			<img class="loading-img" style="display:none;"
				 src="<?php echo plugins_url('static/img/loading.gif', dirname(__FILE__)); ?>"/>
		</div>
		<?php endif; ?>
	</div>
	
</div>
