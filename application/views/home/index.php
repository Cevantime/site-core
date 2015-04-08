<div id="content">
	Hello <?php echo $name; ?> !
	<form action="<?php echo base_url('home/rememberme') ?>" method="POST">
		<label for="username">Your name :</label>
		<input type="text" name="username" id="username"/>
		<input type="submit" name="submit" id="submit" value="Remember me!"/>
	</form>
	
</div>
