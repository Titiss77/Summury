<?php if (isset($errors) && [] !== $errors) { ?>
	<div class="errors" role="alert">
		<ul>
		<?php foreach ($errors as $error) { ?>
			<li><?php echo esc($error); ?></li>
		<?php } ?>
		</ul>
	</div>
<?php } ?>
