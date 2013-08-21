<?php

if (!class_exists('Nexus_Core')) exit();

$this->print_nonce_field();

?>

<div class="nexus-metabox" id="nexus-episode">

	<div class="block">

		<ul>
			<li>
				<label for="nexus-parent-episode"><strong>Parent Episode: </strong></label>
				<input type="text" name="nexus-parent-episode" id="nexus-parent-episode" class="widefat" value="<?php echo(esc_attr($parent_episode_title)); ?>" />
				<?php $this->print_hidden('nexus-parent-episode-id', $parent_episode_id); ?>
			</li>

			<li>
				<label for="nexus-parent-episode"><strong>Fringe Episode: </strong></label>
				<input type="text" name="nexus-fringe-episode" id="nexus-fringe-episode" class="widefat" value="<?php echo(esc_attr($fringe_episode_title)); ?>"  />
				<?php $this->print_hidden('nexus-fringe-episode-id', $fringe_episode_id); ?>
			</li>

			<li>
				<label for="nexus-nsfw-episode"><strong>Not Safe For Work: </strong></label>
				<input type="hidden" name="nexus-nsfw-episode" value="0" /><!-- trickery -->
				<input type="checkbox" name="nexus-nsfw-episode" id="nexus-nsfw-episode" <?php checked(get_post_meta($object->ID, 'nexus-nsfw-episode', true), '1'); ?> value="1" />
			</li>

		</ul>

		<?php if ( $episode_number ): ?>
			<div class="episode-number-meta">
				<h4>Episode Number <?php echo $episode_number; ?></h4>
			</div>
		<?php endif; ?>

	</div>

</div>
<div>
<pre>
<?php print_r(get_post_meta($object->ID)); ?>
</pre>
</div>