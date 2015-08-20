<?php

if (!class_exists('\Nexus\Core')) exit();

?>

<div class="media-tabs">

	<ul class="tabs">
		<?php
			$types = \Nexus\Media::get_instance()->get_media_types();
			foreach ( $types as $type ):
				$type::render_tab();
			endforeach;
		?>
	</ul>

	<?php
		$types = \Nexus\Media::get_instance()->get_media_types();
		foreach ( $types as $type ):
			$type::render_section($this);
		endforeach;
	?>

</div>
