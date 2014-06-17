<div class="hint-info-row error"><div class="error2">
<ul class="messages">
	<?php foreach ($messages as $message): ?>
		<li class="<?php echo $message['type'] ?>">
			<p><?php echo $message['text'] ?></p>
		</li>
	<?php endforeach ?>
</ul>
</div></div>