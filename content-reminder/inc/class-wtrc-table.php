<?php

if (!class_exists('WTRC_List_Table')) {
	require_once('class-wtrc-list-table.php');
}

class WTRC_Table extends WTRC_List_Table
{

	function __construct()
	{
		global $page;

		global $wpdb;
		$this->table = $wpdb->prefix . "contentreminds";

		//Set parent defaults
		parent::__construct(array(
			'singular' => 'remind',     //singular name of the listed records
			'plural'   => 'reminds',    //plural name of the listed records
			'ajax'     => false        //does this table support ajax?
		));
	}

	function column_default($item, $column_name)
	{
		return $item[ $column_name ];
	}

	function column_reminder_email($item)
	{
		return '<a href="mailto:' . $item['reminder_email'] . '">' . $item['reminder_email'] . '</a>';
	}

	function column_post($item)
	{
		$post = get_post($item['post_id']);

		if (is_a($post, 'WP_Post'))
			return '<a href="' . get_edit_post_link($post->ID) . '#wtrc-reminds">' . $post->post_title . '</a>';

		return 'Post Not Found';
	}

	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/
			$item['id']                //The value of the checkbox should be the record's id
		);
	}

	function get_bulk_actions()
	{
		$permission_options = get_option('wtrc_permissions_settings');
		$bulk_action_permission = (isset($permission_options['minimum_role_change'])) ? $permission_options['minimum_role_change'] : 'activate_plugins';
		if (!current_user_can($bulk_action_permission))
			return array();

		$actions = array(
			'delete'        => 'Delete'
		);
		return $actions;
	}

	function prepare_items()
	{
		global $wpdb; //This is used only if making any database queries
		$per_page = 50;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'created'; //If no sort, default to title
		$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
		$query = "SELECT * FROM $this->table ORDER BY $orderby $order";
		$data = $wpdb->get_results($query, ARRAY_A);

		$current_page = $this->get_pagenum();

		$total_items = count($data);

		$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

		$this->items = $data;

		$this->set_pagination_args(array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
		));
	}

	function get_columns()
	{
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'post'     => 'Post',
			'reminder_name'  => 'Name',
			'reminder_email'  => 'Email',
			'comment'  => 'User Comment',
			'created'     => 'Time',
			
		);
		return $columns;
	}

	function get_sortable_columns()
	{
		$sortable_columns = array(
			'created' => array('created', true),     //true means it's already sorted
		);
		return $sortable_columns;
	}

	function process_bulk_action()
	{
		global $wpdb;
		//Detect when a bulk action is being triggered...
		if ('delete' === $this->current_action()) {
			$id_string = join(',', $_GET['remind']);
			$query = "DELETE FROM $this->table WHERE id IN ($id_string)";
			$wpdb->query($query);
		}

	}

}
