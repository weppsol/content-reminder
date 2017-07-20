<?php
/*
Plugin Name: Content Reminder
Plugin URI: https://weppsol.com/
Description: Inserts a secure form on specified pages so that your readers can remind themselves about what they were looking for on your site incase they leave it in the middle. Turn them into returning visitors or even subscribers by putting a "Remind Me" button on any content.
Version: 1.0.0
Author: Weppsol Technologies
Author URI: https://weppsol.com/
License: GPL2
*/

/**********************************************
 *
 * Creating the contentremind table on installation
 *
 ***********************************************/

define('WTRC_TABLE_VERSION', '1.1');

function wtrc_install()
{
	global $wpdb;

	$table_name = wtrc_table_name();
	$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
	$table_up_to_date = get_option("wtrc_db_version") == WTRC_TABLE_VERSION;

	if ($table_exists && $table_up_to_date) {
		return;
	}

	wtrc_create_table();
	update_option('wtrc_db_version', WTRC_TABLE_VERSION);

	if ($table_exists) {
		return;
	}

	wtrc_initialize_settings();
}

function wtrc_table_name()
{
	global $wpdb;
	return $wpdb->prefix . "contentreminds";
}

function wtrc_create_table()
{
	global $wpdb;
	$table_name = wtrc_table_name();

	$charset_collate = $wpdb->get_charset_collate();
	$sql = "
		CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		remind_by bigint(20) NOT NULL,
		created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		comment text DEFAULT '' NULL,
		reminder_name VARCHAR(55) DEFAULT '' NULL,
		reminder_email VARCHAR(55) DEFAULT '' NULL,
		remind_date date DEFAULT NULL,
		remind_time time DEFAULT NULL,
		post_id mediumint(9) NOT NULL,
		UNIQUE KEY id (id) ) $charset_collate;
	";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

function wtrc_initialize_settings()
{
	$wtrc_form_settings = array(
		'active_fields'         => array('reminder_email' => 1, 'reminder_date' => 1, 'reminder_time' => 1),
		'required_fields'       => array('reminder_email' => 1, 'reminder_date' => 1, 'reminder_time' => 1),
		'remind_reasons'        => "Copyright Infringement\nSpam\nInvalid Contents\nBroken Links",
		'slidedown_button_text' => 'Remind Me',
		'submit_button_text'    => 'Send Reminder',
		'color_scheme'          => 'yellow'
	);
	$wtrc_email_settings = array(
		'sender_name'          => get_bloginfo('name'),
		'sender_address'       => get_bloginfo('admin_email'),
		'remind_email_subject' => 'New Reminder - {title}',
		'remind_email_content' => 'Hi {uname},<br><br>Your reminder request from {website} for the {post} has been initiated. Please add this event to your calendar to get a reminder on the desired time.<br><br>Comment:<br>{ucomment}<br><br>Thank you'
	);
	$wtrc_integration_settings = array(
		'integration_type'        => 'automatically',
		'automatic_form_position' => 'above',
		'display_on'              => 'posts_pages'
	);
	$wtrc_permissions_settings = array(
		'minimum_role_view'   => 'install_plugins',
		'minimum_role_change' => 'install_plugins',
		'login_required'      => 0,
		'use_akismet'         => 1
	);
	$wtrc_other_settings = array(
		'disable_metabox'   => 0,
		'disable_db_saving' => 0
	);
	update_option('wtrc_form_settings', $wtrc_form_settings);
	update_option('wtrc_integration_settings', $wtrc_integration_settings);
	update_option('wtrc_email_settings', $wtrc_email_settings);
	update_option('wtrc_permissions_settings', $wtrc_permissions_settings);
	update_option('wtrc_other_settings', $wtrc_other_settings);
}

register_activation_hook(__FILE__, 'wtrc_install');

function wtrc_rollback()
{
	delete_option('wtrc_db_version');
	delete_option('wtrc_form_settings');
	delete_option('wtrc_integration_settings');
	delete_option('wtrc_permissions_settings');
	delete_option('wtrc_other_settings');
	global $wpdb;
	$table_name = $wpdb->prefix . "contentreminds";
	return $wpdb->query("DROP TABLE $table_name");
}

register_uninstall_hook(__FILE__, 'wtrc_rollback');

/**********************************************
 *
 * Enqueuing scripts and styles
 *
 ***********************************************/

function wtrc_enqueue_resources()
{
	wp_enqueue_style('wtrc-style', plugins_url('static/css/styles.css', __FILE__));
	wp_enqueue_script('wtrc-script', plugins_url('static/js/scripts.js', __FILE__), array('jquery'));
	
	wp_enqueue_script('wtrc-datetime-script', plugins_url('static/js/jquery.datetimepicker.min.js', __FILE__), array('jquery'));
	wp_enqueue_style('wtrc-datetime-style', plugins_url('static/css/jquery.datetimepicker.min.css', __FILE__));
	
	wp_localize_script('wtrc-script', 'wtrcajaxhandler', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'wtrc_enqueue_resources');

/**********************************************
 *
 * Automatically insert the remind form in posts
 *
 ***********************************************/

function wtrc_add_remind_button_filter($content)
{
	$integration_options = get_option('wtrc_integration_settings');
	if (($integration_options && $integration_options['integration_type'] == 'manually') ||
		($integration_options['display_on'] == 'single_post' && !is_single()) ||
		($integration_options['display_on'] == 'single_page' && !is_page()) ||
		($integration_options['display_on'] == 'posts_pages' && !is_singular())
	)
		return $content;

	ob_start();
	include "inc/remind-form.php";
	$form_html = ob_get_contents();
	ob_end_clean();

	if ($integration_options && $integration_options['automatic_form_position'] == 'below')
		return $content . $form_html;
	return $form_html . $content;
}

add_filter('the_content', 'wtrc_add_remind_button_filter', 99);

function wtrc_remind_submission_form()
{
	include "inc/remind-form.php";
}

function wtrc_neutralize_excerpt($content)
{
	remove_filter('the_content', 'wtrc_add_remind_button_filter');
	return $content;
}

add_filter('get_the_excerpt', 'wtrc_neutralize_excerpt', 5);

/**********************************************
 *
 * Database functions
 *
 ***********************************************/

function wtrc_insert_data($args)
{
	$other_options = get_option('wtrc_other_settings');
	if ($other_options['disable_db_saving'])
		return true;
	global $wpdb;
	$table = $wpdb->prefix . "contentreminds";
	$result = $wpdb->insert($table, $args);
	if ($result)
		return $wpdb->insert_id;
	return false;
}

function wtrc_get_post_reminds($post_id)
{
	global $wpdb;
	$table = $wpdb->prefix . "contentreminds";
	$query = "SELECT * FROM $table WHERE post_id = $post_id ORDER BY created DESC";
	return $wpdb->get_results($query, ARRAY_A);
}

function wtrc_delete_post_reminds($post_id)
{
	global $wpdb;
	$table = $wpdb->prefix . "contentreminds";
	$query = $wpdb->prepare("DELETE FROM $table WHERE post_id = %d", $post_id);
	return $wpdb->query($query);
}

/**********************************************
 *
 * Cleanup on post deletion
 *
 ***********************************************/

function wtrc_on_post_delete($post_id)
{
	wtrc_delete_post_reminds($post_id);
}

add_action('delete_post', 'wtrc_on_post_delete');

/**********************************************
 *
 * Mailing function
 *
 ***********************************************/

function wtrc_mail($remind)
{
	$post_id = $remind['post_id'];
	$post_url = get_post_permalink($post_id);

	$email_options = get_option('wtrc_email_settings');

	$remind_emails_sent = true;
	$headers = array();

	if ($email_options['sender_name'] && $email_options['sender_address'])
		$headers[] = 'From: ' . $email_options['sender_name'] . ' <' . $email_options['sender_address'] . '>';
		$headers[] = 'Content-Type: text/html';
		
	$remind_string = "\n\nremind:\n\n" . $remind['reason'] . "\n\n" . $remind['comment'];


	$post = get_post($post_id);
	
	$replace_post = '<a href="' . $post_url . '>'. $post->post_title . '</a>';
	$replace_website = '<a href="' . get_site_url() . '>'. get_bloginfo('name') . '</a>';

	$email_options['remind_email_subject'] = str_replace('{title}', $post->post_title , $email_options['remind_email_subject']);
	
	$email_options['remind_email_content'] = str_replace('{uname}', $remind['reminder_name'], $email_options['remind_email_content']);
	$email_options['remind_email_content'] = str_replace('{website}', $replace_website , $email_options['remind_email_content']);
	$email_options['remind_email_content'] = str_replace('{post}', $replace_post, $email_options['remind_email_content']); 
	$email_options['remind_email_content'] = str_replace('{ucomment}', $remind['comment'], $email_options['remind_email_content']);

	// For ics file
	$dtstart	= $remind['remind_date'] . ' '. $remind['remind_time'] . ':00';
				
	header('Content-type: text/calendar; charset=utf-8');
	
	$ics = new ICS(array(
	  'location' => $post_url,
	  'description' => $remind['comment'],
	  'dtstart' => $dtstart,
	  'dtend' => $dtstart,
	  'summary' => $post->post_title,
	  'url' => $post_url
	));
	
	$ics_file_contents =  $ics->to_string();
	
	$target = ABSPATH. 'wp-content/uploads/ics_' . $remind['id'] . '.ics';
	
	file_put_contents($target, $ics_file_contents);
	
	$attachments = $target;
	
	$remind_emails_sent = wp_mail($remind['reminder_email'], $email_options['remind_email_subject'], $email_options['remind_email_content'], $headers, $attachments);
	
	wp_delete_file($target);
	
	return ($remind_emails_sent);
}

/**********************************************
 *
 * Check for errors, insert into DB and send emails
 *
 ***********************************************/

function wtrc_add_remind()
{
	$message['success'] = 0;
	$permissions = get_option('wtrc_permissions_settings');
	if ($permissions['login_required'] && !is_user_logged_in()) {
		$message['message'] = 'To submit a remind you need to <a href="<?php echo wp_login_url(); ?>" title="Login">login</a> first';
		die(json_encode($message));
	}

	$form_options = get_option('wtrc_form_settings');
	$active_fields = $form_options['active_fields'];
	$required_fields = $form_options['required_fields'];
	
	foreach ($required_fields as $key => $field) {
		if ($field && $active_fields[ $key ] && !$_POST[ $key ]) {
			$message['message'] = 'You missed a mandatory field';
			die(json_encode($message));
		}
	}

	if ($active_fields['reminder_email'] && $_POST['reminder_email'] && !is_email($_POST['reminder_email'])) {
		$message['message'] = 'Email address invalid';
		die(json_encode($message));
	}

	$comment = $_POST['comment'];
	$reminder_name = (isset($_POST['reminder_name'])) ? $_POST['reminder_name'] : '';
	$reminder_email = (isset($_POST['reminder_email'])) ? $_POST['reminder_email'] : '';
	
	$remind_date = $_POST['reminder_date'];
	$remind_time = $_POST['reminder_time'];
	
	$current_user = wp_get_current_user();
	
	$new_remind = array(
		'remind_by'		 =>	$current_user->ID,
		'created'        => current_time('mysql'),
		'comment'        => sanitize_text_field($comment),
		'reminder_name'  => sanitize_text_field($reminder_name),
		'reminder_email' => sanitize_email($reminder_email),
		'remind_date'	 => $remind_date,
		'remind_time'	 => $remind_time,
		'post_id'        => intval($_POST['id']),
	);
	
	if (wtrc_is_spam($new_remind)) {
		$message['message'] = 'Your submission has been marked as spam by our filters';
		die(json_encode($message));
	}
	
	$insert_result = wtrc_insert_data($new_remind);
	if (!$insert_result) {
		$message['message'] = 'An unexpected error occured. Please try again later';
		die(json_encode($message));
	}
	else
	{
		$new_remind['id'] = $insert_result;
	}

	wtrc_mail($new_remind);
	$message['success'] = 1;
	$message['message'] = 'Your reminder has been set! Please check your email to add it to your calendar.';
	die(json_encode($message));
}

add_action('wp_ajax_wtrc_add_remind', 'wtrc_add_remind');
add_action('wp_ajax_nopriv_wtrc_add_remind', 'wtrc_add_remind');

/**********************************************
 *
 * Adding new columns to edit.php page
 *
 ***********************************************/

function wtrc_add_admin_column_headers($headers)
{
	$permission_options = get_option('wtrc_permissions_settings');
	if (!current_user_can($permission_options['minimum_role_view'])) return $headers;

	$headers['wtrc_post_reminds'] = "Post reminds";
	return $headers;
}

add_filter('manage_posts_columns', 'wtrc_add_admin_column_headers', 10, 2);

function wtrc_add_admin_column_contents($header, $something)
{
	if ($header == 'wtrc_post_reminds') {
		global $post;
		$wtrc_post_reminds = wtrc_get_post_reminds($post->ID);
		echo '<a href="' . get_edit_post_link($post->ID) . '#wtrc-reminds">' . count($wtrc_post_reminds) . '</a>';
	}
}

add_filter('manage_posts_custom_column', 'wtrc_add_admin_column_contents', 10, 2);

/**********************************************
 *
 * Prepare the remind for akismet and run tests
 *
 ***********************************************/

function wtrc_is_spam($remind)
{
	$permission_options = get_option('wtrc_permissions_settings');
	if (!$permission_options['use_akismet'] || !function_exists('akismet_init'))
		return false;
	$content['comment_author'] = $remind['reminder_name'];
	$content['comment_author_email'] = $remind['reminder_email'];
	$content['comment_content'] = $remind['comment'];
	if (wtrc_akismet_failed($content))
		return true;
	return false;
}

/**********************************************
 *
 * Pass the remind through Akismet filters to
 * make sure it isn't spam
 *
 ***********************************************/

function wtrc_akismet_failed($content)
{
	$isSpam = FALSE;
	$content = (array)$content;
	if (function_exists('akismet_init')) {
		$wpcom_api_key = get_option('wordpress_api_key');
		if (!empty($wpcom_api_key)) {
			global $akismet_api_host, $akismet_api_port;
			// set remaining required values for akismet api
			$content['user_ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
			$content['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$content['referrer'] = $_SERVER['HTTP_REFERER'];
			$content['blog'] = get_option('home');

			if (empty($content['referrer'])) {
				$content['referrer'] = get_permalink();
			}

			$queryString = '';

			foreach ($content as $key => $data) {
				if (!empty($data)) {
					$queryString .= $key . '=' . urlencode(stripslashes($data)) . '&';
				}
			}
			$response = akismet_http_post($queryString, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
			if ($response[1] == 'true') {
				update_option('akismet_spam_count', get_option('akismet_spam_count') + 1);
				$isSpam = TRUE;
			}
		}
	}
	return $isSpam;
}

/**********************************************
 *
 * Include the necessary items
 *
 ***********************************************/

include('inc/meta-boxes.php');

include('inc/reminds-list.php');

include('inc/options-panel.php');

include('inc/ics.php');

