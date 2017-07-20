<?php

/*--------------------------------------------------
	Registering Meta Boxes
----------------------------------------------------*/

add_action('load-post.php', 'wtrc_post_meta_boxes_setup');
add_action('load-post-new.php', 'wtrc_post_meta_boxes_setup');

function wtrc_post_meta_boxes_setup()
{
	$other_options = get_option('wtrc_other_settings');
	$permission_options = get_option('wtrc_permissions_settings');
	if ($other_options['disable_metabox'] || !current_user_can($permission_options['minimum_role_view'])) return;
	add_action('add_meta_boxes', 'wtrc_add_post_meta_boxes');
}

function wtrc_add_post_meta_boxes()
{
	add_meta_box(
		'wtrc-post-reminds',            // Unique ID
		esc_html__('Post Reminds', 'wtrc'),        // Title
		'wtrc_meta_box_callback',        // Callback function
		'post',                    // Admin page (or post type)
		'normal',                    // Context
		'default'                    // Priority
	);
}

function wtrc_meta_box_callback($object, $box)
{
	$post_reminds = wtrc_get_post_reminds($object->ID);
	if (count($post_reminds) <= 0):
		?>
		No reminds found.
	<?php else: ?>
		<style type="text/css">
			#wtrc-reminds {
				width: 100%;
				border-collapse: collapse;
				text-align: left;
			}

			#wtrc-reminds td, #wtrc-reminds th {
				padding: 6px;
				min-width: 160px;
				border-bottom: 1px solid #E7E7E7;
			}

			#wtrc-reminds .even {
				background: #e7e7e7;
			}

			#wtrc-reminds th {
				font-size: medium;
			}
		</style>
		<table id="wtrc-reminds">
			<tr>
				<th>Issue</th>
				<th>Details</th>
			</tr>
			<?php foreach ($post_reminds as $key => $remind): ?>
				<tr class="<?php echo ($key % 2 == 0) ? 'even' : 'odd'; ?>">
					<td><?php echo $remind['reason']; ?></td>
					<td><?php echo $remind['details']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	endif;
}

?>
