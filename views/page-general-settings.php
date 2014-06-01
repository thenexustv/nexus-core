<?php

?>
<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<p>View and edit the general settings for this Podcast Network.</p>

	<form method="post" action="options.php">

		<?php
			settings_fields($this->get_settings()->get_key());
			do_settings_sections($this->page_hook);
		?>

		<?php submit_button(); ?>

	</form>

</div>
