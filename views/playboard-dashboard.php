<?php if (!class_exists('Nexus_Core')) exit(); ?>

<div id="playboard">
	<div class="inner">

		<dl class="data-list">
			<?php foreach ($playboard['series'] as $show): ?>

			<div class="combine">
				<dt class="series-short-name"><?php echo $show['slug']; ?></dt>
				<dd class="series-episode-count"><?php echo $show['count']; ?></dd>
			</div>

			<?php endforeach; ?>
		</dl>
		
		<dl class="stats-list">
			<dt>Total</dt>
			<dd><?php echo($playboard['total_all']); ?></dd>
			<dt>90 Days</dt>
			<dd><?php echo($playboard['total_ninety']); ?></dd>
			<dt>30 Days</dt>
			<dd><?php echo($playboard['total_thirty']); ?></dd>
			<dt>7 Days</dt>
			<dd><?php echo($playboard['total_seven']); ?></dd>
		</dl>
		
		<div class="meta">
			<p class="datetime" title="<?php echo(date('l jS \of F Y h:i:s A', $playboard['last_update'])); ?>"><?php echo(date('l, F jS, Y', $playboard['last_update'])); ?></p>
		</div>

	</div>
</div>