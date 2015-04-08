<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-1.9.1.js"></script>
		<script type="text/javascript">
			var baseURL = "<?php echo base_url(); ?>";
		</script>
		<script type="text/javascript" src="<?php echo base_url() ?>js/pagination.js"></script>

		<?php echo $css_for_layout ?>

		<?php echo $js_for_layout ?>

        <title><?php echo $title_for_layout ?></title>
    </head>
    <body>
<!-- ACTIVATE THIS IF YOU HAVE BOOTSRAP INSTALLED. OTHERWISE YOU CAN JUST DROP IT !        

	<div id="conteneur">
			<?php if (isset($errors)): ?>
				<div class="alert alert-error">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<h4>Error!</h4>
					<?php echo $errors; ?>
				</div>
			<?php endif; ?>
			<?php if (isset($warnings)): ?>
				<div class="alert alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<h4>Warning!</h4>
					<?php echo $warnings; ?>
				</div>
			<?php endif; ?>
			<?php if (isset($success)): ?>
				<div class="alert alert-success">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<h4>Success!</h4>
					<?php echo $success; ?>
				</div>
			<?php endif; ?>
            <div id="modal-from-dom" class="modal hide fade">
                <div class="modal-header">
                    <a href="#" class="close" data-dismiss="modal">&times;</a>
                    <h3></h3>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <a href="" class="btn btn-danger">Ouais, je suis un fou</a>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
            <script type="text/javascript">
			$(function() {
				$('#modal-from-dom').bind('show', function() {
					var url = $(this).data('url'),
							removeBtn = $(this).find('.btn-danger');

					var body = $(this).find(".modal-body");
					body.html($(this).data('body'));

					var header = $(this).find(".modal-header h3");
					header.html($(this).data('header'));

					removeBtn.attr('href', url);
				});


				$('.confirm').click(function(e) {
					var url = $(this).data('url');
					var body = $(this).data('body');
					var header = $(this).data('header');
					$('#modal-from-dom')
							.data('url', url)
							.data('body', body)
							.data('header', header)
							.modal('show');
				});
			});
            </script>-->
            <div><?php echo $content_for_layout ?></div>

        </div> <!--  le conteneur principal -->
    </body>
</html>
