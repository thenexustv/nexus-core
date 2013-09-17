<?php
	if (!class_exists('Nexus_Core')) exit();

	$posts = array( $recent['show'][0], $recent['fringe'][0] );

?>

<div id="most-recent">
	<div class="inner">

	<?php foreach ($posts as $post): ?>
		<div class="episode">
			<h4 class="title-bag"><a href="<?php echo(get_permalink($post['ID'])); ?>"><?php echo(Nexus_Episode::format_episode_title($post['ID'])); ?></a></h4>
			<div><p><?php echo($post['post_excerpt']); ?></p></div>
			<div class="meta">
				
				<div class="edit">
					<a href="<?php echo(get_edit_post_link($post['ID'])); ?>">Edit</a>
				</div>
				<div class="datetime">
					<?php echo Nexus_Utility::human_time_difference( strtotime($post['post_date']), current_time('timestamp') ) . ' ago'; ?>
				</div>
				
				<br class="clear" />

			</div>
		</div>
		
	<?php endforeach; ?>

	</div>
</div>