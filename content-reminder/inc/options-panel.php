<?php
/*
The settings page
*/

function wtrc_menu_item()
{
	global $wtrc_settings_page_hook;
	$wtrc_settings_page_hook = add_submenu_page(
		'wtrc_reminds_page',
		'Content Reminder Settings',                            // The title to be displayed in the browser window for this page.
		'Settings',                                    // The text to be displayed for this menu item
		'administrator',                            // Which type of users can see this menu item
		'wtrc_settings',                            // The unique ID - that is, the slug - for this menu item
		'wtrc_render_settings_page'                // The name of the function to call when rendering this menu's page
	);
}

add_action('admin_menu', 'wtrc_menu_item');

function wtrc_scripts_styles($hook)
{
	global $wtrc_settings_page_hook;
	if ($wtrc_settings_page_hook != $hook)
		return;
	wp_enqueue_style("options_panel_stylesheet", plugins_url("static/css/options-panel.css", dirname(__FILE__)), false, "1.0", "all");
	wp_enqueue_script("options_panel_script", plugins_url("static/js/options-panel.js", dirname(__FILE__)), false, "1.0");
	wp_enqueue_script('common');
	wp_enqueue_script('wp-lists');
	wp_enqueue_script('postbox');
}

add_action('admin_enqueue_scripts', 'wtrc_scripts_styles');

function wtrc_render_settings_page()
{
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2>Content Reminder Settings</h2>
		<?php settings_errors(); ?>
		<div class="clearfix paddingtop20">
			<div class="first ninecol">
				<form method="post" action="options.php">
					<?php settings_fields('wtrc_settings'); ?>
					<?php do_meta_boxes('wtrc_metaboxes', 'advanced', null); ?>
					<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
					<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
				</form>
			</div>
			<div class="last threecol">
				<div class="side-block">
					Like the plugin? <br/>
					<a href="https://wordpress.org/support/view/plugin-reviews/wp-remind-content#postform">Leave a
						review</a>.
				</div>
			</div>
		</div>
	</div>
<?php }

function wtrc_create_options()
{

	add_settings_section('form_settings_section', null, null, 'wtrc_settings');
	add_settings_section('integration_settings_section', null, null, 'wtrc_settings');
	add_settings_section('email_settings_section', null, null, 'wtrc_settings');
	add_settings_section('permissions_settings_section', null, null, 'wtrc_settings');
	add_settings_section('other_settings_section', null, null, 'wtrc_settings');

	add_settings_field(
		'active_fields', '', 'wtrc_render_settings_field', 'wtrc_settings', 'form_settings_section',
		array(
			'title' => 'Active Fields',
			'desc'  => 'Fields that will appear on the remind form',
			'id'    => 'active_fields',
			'type'  => 'multicheckbox',
			'items' => array('reminder_name' => 'Name', 'reminder_email' => 'Email', 'reminder_date' => 'Date', 'reminder_time' => 'Time','comment' => 'Comment'),
			'group' => 'wtrc_form_settings'
		)
	);	

	add_settings_field(
		'required_fields', '', 'wtrc_render_settings_field', 'wtrc_settings', 'form_settings_section',
		array(
			'title' => 'Mandatory Fields',
			'desc'  => 'Fields that are mandatory',
			'id'    => 'required_fields',
			'type'  => 'multicheckbox',
			'items' => array('reminder_name' => 'Name', 'reminder_email' => 'Email', 'reminder_date' => 'Date', 'reminder_time' => 'Time','comment' => 'Comment'),
			'group' => 'wtrc_form_settings'
		)
	);

	add_settings_field(
		'slidedown_button_text', '', 'wtrc_render_settings_field', 'wtrc_settings', 'form_settings_section',
		array(
			'title' => 'Slide Down Button Text',
			'desc'  => '',
			'id'    => 'slidedown_button_text',
			'type'  => 'text',
			'group' => 'wtrc_form_settings'
		)
	);

	add_settings_field(
		'submit_button_text', '', 'wtrc_render_settings_field', 'wtrc_settings', 'form_settings_section',
		array(
			'title' => 'Submit Button Text',
			'desc'  => '',
			'id'    => 'submit_button_text',
			'type'  => 'text',
			'group' => 'wtrc_form_settings'
		)
	);

	add_settings_field(
		'color_scheme', '', 'wtrc_render_settings_field', 'wtrc_settings', 'form_settings_section',
		array(
			'title'   => 'Color Scheme',
			'desc'    => 'Select a scheme for button and form',
			'id'      => 'color_scheme',
			'type'    => 'select',
			'options' => array("yellow-colorscheme" => "Yellow", "red-colorscheme" => "Red", "blue-colorscheme" => "Blue", "green-colorscheme" => "Green"),
			'group'   => 'wtrc_form_settings'
		)
	);

	add_settings_field(
		'integration_type', '', 'wtrc_render_settings_field', 'wtrc_settings', 'integration_settings_section',
		array(
			'title'   => 'Add the remind form',
			'desc'    => 'If you choose manual integration you will have to place <b>&lt;?php wtrc_remind_submission_form(); ?&gt;</b> in your theme files manually.',
			'id'      => 'integration_type',
			'type'    => 'select',
			'options' => array("automatically" => "Automatically", "manually" => "Manually"),
			'group'   => 'wtrc_integration_settings'
		)
	);

	add_settings_field(
		'automatic_form_position', '', 'wtrc_render_settings_field', 'wtrc_settings', 'integration_settings_section',
		array(
			'title'   => 'Add the form',
			'desc'    => ' Where do you want the form to be placed? This option will only work if you choose automatic integration',
			'id'      => 'automatic_form_position',
			'type'    => 'select',
			'options' => array("above" => "Above post content", "below" => "Below post content"),
			'group'   => 'wtrc_integration_settings'
		)
	);

	add_settings_field(
		'display_on', '', 'wtrc_render_settings_field', 'wtrc_settings', 'integration_settings_section',
		array(
			'title'   => 'Display form on',
			'desc'    => ' Select the section of your website where you want this form to appear',
			'id'      => 'display_on',
			'type'    => 'select',
			'options' => array("everywhere" => "The whole site", "single_post" => "Posts", 'single_page' => 'Pages', 'posts_pages' => 'Posts & Pages'),
			'group'   => 'wtrc_integration_settings'
		)
	);
	
	add_settings_field(
		'sender_name', '', 'wtrc_render_settings_field', 'wtrc_settings', 'email_settings_section',
		array(
			'title' => 'Sender\'s Name',
			'desc'  => '',
			'id'    => 'sender_name',
			'type'  => 'text',
			'group' => 'wtrc_email_settings'
		)
	);

	add_settings_field(
		'sender_address', '', 'wtrc_render_settings_field', 'wtrc_settings', 'email_settings_section',
		array(
			'title' => 'Sender\'s Email Address',
			'desc'  => '',
			'id'    => 'sender_address',
			'type'  => 'text',
			'group' => 'wtrc_email_settings'
		)
	);

	add_settings_field(
		'remind_email_subject', '', 'wtrc_render_settings_field', 'wtrc_settings', 'email_settings_section',
		array(
			'title' => 'Remind Email Subject',
			'desc'  => 'Subject of the email you want sent to reminded request user. <b>{title}</b> will be replaced by Post title',
			'id'    => 'remind_email_subject',
			'type'  => 'text',
			'group' => 'wtrc_email_settings'
		)
	);

	add_settings_field(
		'remind_email_content', '', 'wtrc_render_settings_field', 'wtrc_settings', 'email_settings_section',
		array(
			'title' => 'Remind Email Content',
			'desc'  => 'This will be sent to the reminded request user. <br/><b>{uname}</b> will be replaced by remind user name<br/><b>{website}</b> will be replaced with a link and name to website<br/><b>{post}</b> will be replaced with a link and name to post<br/><b>{ucomment}</b> will be replaced with a user comment',
			'id'    => 'remind_email_content',
			'type'  => 'textarea',
			'group' => 'wtrc_email_settings'
		)
	);
	
	add_settings_field(
		'minimum_role_view', '', 'wtrc_render_settings_field', 'wtrc_settings', 'permissions_settings_section',
		array(
			'title'   => 'Minimum access level required to view the reminds',
			'desc'    => 'What\'s the minimum role that a logged in user needs to have in order to view reminds',
			'id'      => 'minimum_role_view',
			'type'    => 'select',
			'options' => array("install_plugins" => "Administrator", "moderate_comments" => "Editor", "edit_published_posts" => "Author", "edit_posts" => "Contributor", "read" => "Subscriber"),
			'group'   => 'wtrc_permissions_settings'
		)
	);

	add_settings_field(
		'minimum_role_change', '', 'wtrc_render_settings_field', 'wtrc_settings', 'permissions_settings_section',
		array(
			'title'   => 'Minimum access level required to change status of/delete reminds',
			'desc'    => 'What\'s the minimum role that a logged in user needs to have in order to manipulate reminds',
			'id'      => 'minimum_role_change',
			'type'    => 'select',
			'options' => array("install_plugins" => "Administrator", "moderate_comments" => "Editor", "edit_published_posts" => "Author", "edit_posts" => "Contributor", "read" => "Subscriber"),
			'group'   => 'wtrc_permissions_settings'
		)
	);

	add_settings_field(
		'login_required', '', 'wtrc_render_settings_field', 'wtrc_settings', 'permissions_settings_section',
		array(
			'title' => 'Users must be logged in to remind content',
			'desc'  => '',
			'id'    => 'login_required',
			'type'  => 'checkbox',
			'group' => 'wtrc_permissions_settings'
		)
	);

	add_settings_field(
		'use_akismet', '', 'wtrc_render_settings_field', 'wtrc_settings', 'permissions_settings_section',
		array(
			'title' => 'Use Akismet to filter reminds',
			'desc'  => 'Akismet plugin is required for this feature.',
			'id'    => 'use_akismet',
			'type'  => 'checkbox',
			'group' => 'wtrc_permissions_settings'
		)
	);

	add_settings_field(
		'disable_metabox', '', 'wtrc_render_settings_field', 'wtrc_settings', 'other_settings_section',
		array(
			'title' => 'Disable metabox?',
			'desc'  => 'Check if you don\'t want to display the metabox',
			'id'    => 'disable_metabox',
			'type'  => 'checkbox',
			'group' => 'wtrc_other_settings'
		)
	);

	add_settings_field(
		'disable_db_saving', '', 'wtrc_render_settings_field', 'wtrc_settings', 'other_settings_section',
		array(
			'title' => 'Don\'t save reminders in database',
			'desc'  => 'Check if you don\'t want to save reminders in database',
			'id'    => 'disable_db_saving',
			'type'  => 'checkbox',
			'group' => 'wtrc_other_settings'
		)
	);

	// Finally, we register the fields with WordPress
	register_setting('wtrc_settings', 'wtrc_form_settings', 'wtrc_settings_validation');
	register_setting('wtrc_settings', 'wtrc_integration_settings', 'wtrc_settings_validation');
	register_setting('wtrc_settings', 'wtrc_email_settings', 'wtrc_settings_validation');
	register_setting('wtrc_settings', 'wtrc_permissions_settings', 'wtrc_settings_validation');
	register_setting('wtrc_settings', 'wtrc_other_settings', 'wtrc_settings_validation');

} // end sandbox_initialize_theme_options 
add_action('admin_init', 'wtrc_create_options');

function wtrc_settings_validation($input)
{
	return $input;
}

function wtrc_add_meta_boxes()
{
	add_meta_box("wtrc_form_settings_metabox", 'Form Settings', "wtrc_metaboxes_callback", "wtrc_metaboxes", 'advanced', 'default', array('settings_section' => 'form_settings_section'));
	add_meta_box("wtrc_integration_settings_metabox", 'Integration Settings', "wtrc_metaboxes_callback", "wtrc_metaboxes", 'advanced', 'default', array('settings_section' => 'integration_settings_section'));
	add_meta_box("wtrc_email_settings_metabox", 'Email Settings', "wtrc_metaboxes_callback", "wtrc_metaboxes", 'advanced', 'default', array('settings_section' => 'email_settings_section'));
	add_meta_box("wtrc_permissions_settings_metabox", 'Security Settings', "wtrc_metaboxes_callback", "wtrc_metaboxes", 'advanced', 'default', array('settings_section' => 'permissions_settings_section'));
	add_meta_box("wtrc_other_settings_metabox", 'Other Settings', "wtrc_metaboxes_callback", "wtrc_metaboxes", 'advanced', 'default', array('settings_section' => 'other_settings_section'));
}

add_action('admin_init', 'wtrc_add_meta_boxes');

function wtrc_metaboxes_callback($post, $args)
{
	do_settings_fields("wtrc_settings", $args['args']['settings_section']);
	submit_button('Save Changes', 'secondary');
}

function wtrc_render_settings_field($args)
{
	$option_value = get_option($args['group']);
	?>
	<div class="row clearfix">
		<div class="col colone"><?php echo $args['title']; ?></div>
		<div class="col coltwo">
			<?php if ($args['type'] == 'text'): ?>
				<input type="text" id="<?php echo $args['id'] ?>"
					   name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
					   value="<?php echo esc_attr($option_value[ $args['id'] ]); ?>">
			<?php elseif ($args['type'] == 'select'): ?>
				<select name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>" id="<?php echo $args['id']; ?>">
					<?php foreach ($args['options'] as $key => $option) { ?>
						<option <?php selected($option_value[ $args['id'] ], $key);
						echo 'value="' . $key . '"'; ?>><?php echo $option; ?></option><?php } ?>
				</select>
			<?php elseif ($args['type'] == 'checkbox'): ?>
				<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>" value="0"/>
				<input type="checkbox" name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
					   id="<?php echo $args['id']; ?>" value="1" <?php checked($option_value[ $args['id'] ]); ?> />
			<?php elseif ($args['type'] == 'textarea'): ?>
				<textarea name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
						  type="<?php echo $args['type']; ?>" cols=""
						  rows=""><?php if ($option_value[ $args['id'] ] != "") {
						echo stripslashes(esc_textarea($option_value[ $args['id'] ]));
					} ?></textarea>
			<?php elseif ($args['type'] == 'multicheckbox'):
				foreach ($args['items'] as $key => $checkboxitem):
					?>
					<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   value="0"/>
					<label
						for="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"><?php echo $checkboxitem; ?></label>
					<input type="checkbox" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   id="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>" value="1"
						   <?php 
						   $disble = array("reminder_email", "reminder_date", "reminder_time");
						   if (in_array($key, $disble)){ ?>checked="checked" disabled="disabled" <?php } else {
						checked($option_value[ $args['id'] ][ $key ]);
					} ?> />
				<?php endforeach; ?>
			<?php elseif ($args['type'] == 'multitext'):
				foreach ($args['items'] as $key => $textitem):
					?>
					<label
						for="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"><?php echo $textitem; ?></label>
					<br/>
					<input type="text" id="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   value="<?php echo esc_attr($option_value[ $args['id'] ][ $key ]); ?>"><br/>
				<?php endforeach; endif; ?>
		</div>
		<div class="col colthree">
			<small><?php echo $args['desc'] ?></small>
		</div>
	</div>
	<?php
}

?>
