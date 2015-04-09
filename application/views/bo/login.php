<div class="dialog">
    <div class="panel panel-default">
        <p class="panel-heading no-collapse">Sign In</p>
        <div class="panel-body">
            <form action="<?php echo base_url('bo/login') ?>" method="POST">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" class="form-control span12" name="login" id="password">
                </div>
                <div class="form-group">
					<label>Mot de Passe</label>
					<input type="password" class="form-control span12 form-control" name="password" id="password">
                </div>
                <input type="submit" class="btn btn-primary pull-right" value="Se connecter">
                <!--<label class="remember-me"><input type="checkbox"> Remember me</label>-->
                <div class="clearfix"></div>
            </form>
        </div>
    </div>
    <!--<p><a href="reset-password.html">Forgot your password?</a></p>-->
</div>
