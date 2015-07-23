<div class="btn-toolbar list-toolbar">
    <a class="btn btn-primary" href="bo/users/add"><i class="fa fa-plus"></i> Nouvel utilisateur</a>
	<!--    <button class="btn btn-default">Import</button>
		<button class="btn btn-default">Export</button>-->
	<div class="btn-group">
	</div>
</div>
<table class="table">
	<thead>
		<tr>
			<th>#</th>
			<th>Login</th>
			<th>Email</th>
			<th style="width: 3.5em;"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo $user->id ?></td>
				<td><?php echo $user->login ?></td>
				<td><?php echo $user->email ?></td>
				<td>
					<a href="user.html"><i class="fa fa-pencil"></i></a>
					<a href="#" class="confirm" 
					   data-url="<?php echo base_url('bo/users/delete/'.$user->id) ?>" 
					   data-header="Suppression d'un utilisateur" 
					   data-body="<p>Attention!</p><p>Vous êtes sur le point de supprimer un utilisateur.</p><p>Continuer?</p> ">
						<i class="fa fa-trash-o"></i>
					</a>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
<?php echo pagination($max, 'bo/users/all', $start, $offset); ?>

