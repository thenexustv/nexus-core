<?php

if (!class_exists('Nexus_Core')) exit();

?>
<input type="hidden" name="term_meta[force]" value="1" />
<tr class="form-field">
<th scope="row" valign="top"><label for="term_meta[retired]">Retired </label></th>
	<td>
		<input type="checkbox" name="term_meta[retired]" id="term_meta[retired]" <?php checked($term_meta['retired'], 1); ?> value="1">
		<p class="description">Is this series retired and no longer being produced?</p>
	</td>
</tr>