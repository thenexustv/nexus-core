<?php if (!class_exists('Nexus_Core')) exit(); ?>

<div id="most-recent">
	<div class="inner">

	<?php foreach ($recent['show'] as $post): ?>
		<div class="episode">
			<h4 class="title-bag"><a href="<?php echo(get_permalink($post['ID'])); ?>"><?php echo($core->format_episode_title($post['ID'])); ?></a></h4>
			<div><p><?php echo($post['post_excerpt']); ?></p></div>
			<div class="meta">
				
				<div class="edit">
					<a href="<?php echo(get_edit_post_link($post['ID'])); ?>">Edit</a>
				</div>
				<div class="datetime">
					<?php /*echo(human_time_difference( strtotime($post['post_date']), current_time('timestamp') ) . ' ago');*/ ?>
				</div>

			</div>
		</div>
	<?php endforeach; ?>

	<br class="clear" />
	<hr  />

	<?php foreach ($recent['fringe'] as $post): ?>
		<div class="episode">
			<h4 class="title-bag"><a href="<?php echo(get_permalink($post['ID'])); ?>"><?php echo($core->format_episode_title($post['ID'])); ?></a></h4>
			<div><p><?php echo($post['post_excerpt']); ?></p></div>
			<div class="meta">
				
				<div class="edit">
					<a href="<?php echo(get_edit_post_link($post['ID'])); ?>">Edit</a>
				</div>
				<div class="datetime">
					<?php /*echo(human_time_difference( strtotime($post['post_date']), current_time('timestamp') ) . ' ago');*/ ?>
				</div>

			</div>
		</div>
	<?php endforeach; ?>

	<br class="clear" />

	</div>
</div>