<?php

if (!class_exists('Nexus_Core')) exit();

$this->print_nonce_field();

?>

<div class="nexus-metabox" id="nexus-episode-people">

	<div class="block">
		<p><label for="nexus-episode-people-input"><strong>Episode Members: </strong></label></p>
		<div class="container">

			<div id="people-list">
				<script type="application/javascript" id="people-list-inflate">
					<?php if (!empty($members) && is_array($members)):
							echo json_encode($members);
						else:
							echo json_encode(array());
						endif;
					?>
				</script>
			</div>

			<div><input type="text" id="nexus-episode-people-input" autocomplete="off" class="widefat" placeholder="Enter a person's name here" /></div>

			<?php if ($has_duplicates): ?>
				<div class="debug-hidden error-message"><p>Duplicate members dectected!</p></div>
			<?php endif; ?>

		</div>

	</div>

</div>
<div>
<pre>
<?php /*print_r(get_post_meta($object->ID));*/ ?>
</pre>
</div>