<?php

if (!class_exists('WTRC_Table')) {
	require_once('class-wtrc-table.php');
}

function wtrc_add_menu_items()
{
	$permission_options = get_option('wtrc_permissions_settings');
	$menu_page_permission = (isset($permission_options['minimum_role_view'])) ? $permission_options['minimum_role_view'] : 'activate_plugins';
	add_menu_page('Content Reminder', 'Content Reminder', $menu_page_permission, 'wtrc_reminds_page', 'wtrc_render_list_page','dashicons-clock');
}

add_action('admin_menu', 'wtrc_add_menu_items');

function wtrc_db_change_admin_notice()
{
	$message = '';
	if (!isset($_GET['remind']) || !isset($_GET['action']))
		return;
	if ($_GET['action'] === 'delete')
		$message = count($_GET['remind']) . " record(s) deleted from database";
	?>
	<div class="updated">
		<p><?php echo $message; ?></p>
	</div>
	<?php
}

add_action('admin_notices', 'wtrc_db_change_admin_notice');

function wtrc_render_list_page()
{
	$reportsTable = new WTRC_Table();
	$reportsTable->prepare_items();
	?>
	<div class="wrap">
		<div id="icon-users" class="icon32"><br/></div>
		<h2>Content Reminders</h2>
		<form id="reports-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
			<?php $reportsTable->display() ?>
		</form>
	</div>
	<?php
}
