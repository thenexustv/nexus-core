<?php

if (!class_exists('Nexus_Core')) exit();

$this->print_nonce_field();

?>

<div class="nexus-metabox" id="nexus-episode">

	<div class="block">

		<ul>


			<li>
				<label for="nexus-people-email"><strong>Email Address: </strong></label>
				<input type="text" name="nexus-people-email" id="nexus-people-email" class="widefat" <?php $this->print_meta_value($object, 'nexus-people-email'); ?> />
			</li>
			
			<li>
				<label for="nexus-people-twitter-url"><strong>Twitter URL: </strong></label>
				<input type="text" name="nexus-people-twitter-url" id="nexus-people-twitter-url" class="widefat" <?php $this->print_meta_value($object, 'nexus-people-twitter-url'); ?> />
			</li>

			<li>
				<label for="nexus-people-googleplus-url"><strong>Google+ URL: </strong></label>
				<input type="text" name="nexus-people-googleplus-url" id="nexus-people-googleplus-url" class="widefat" <?php $this->print_meta_value($object, 'nexus-people-googleplus-url'); ?> />
			</li>

			<li>
				<label for="nexus-people-website-url"><strong>Website URL: </strong></label>
				<input type="text" name="nexus-people-website-url" id="nexus-people-website-url" class="widefat" <?php $this->print_meta_value($object, 'nexus-people-website-url'); ?> />
			</li>

			<li>
				<label for="nexus-people-host"><strong>Is Host: </strong></label>
				<input type="hidden" name="nexus-people-host" value="0" /><!-- trickery -->
				<input type="checkbox" name="nexus-people-host" id="nexus-people-host" <?php checked(get_post_meta($object->ID, 'nexus-people-host', true), '1'); ?> value="1" />
			</li>

		</ul>

	</div>

</div>
<div>
<pre>
<?php /*print_r(get_post_meta($object->ID));*/ ?>
</pre>
</div>