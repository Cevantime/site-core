<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript">
			var baseURL = "<?php echo base_url(); ?>";
		</script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-1.9.1.js"></script>
		<?php echo $css_for_layout ?>
		<?php echo $js_for_layout ?>
        <title><?php echo $title_for_layout ?></title>
    </head>
    <body>
        <div id="wrap">
            <div id="main">
				<?php echo $content_for_layout ?>
            </div>
        </div>
    </body>
</html>