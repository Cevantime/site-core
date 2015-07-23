<div id="messageContainer">
	<?php if (isset($errors)): ?>
		<div class="mess alert-error">
			<?php echo $errors; ?>
		</div>
	<?php endif; ?>
	<?php if (isset($warnings)): ?>
		<div class="mess alert-block">
			<?php echo $warnings; ?>
		</div>
	<?php endif; ?>
	<?php if (isset($success)): ?>
		<div class="mess alert-success">
			<?php echo $success; ?>
		</div>
	<?php endif; ?>
</div>

<script type="text/javascript">
	
	$(function(){
		$('.mess').popup({
			deleteOriginal : true,
			auto : true,
			wrapHeight: '100%'
		});
	});
</script>