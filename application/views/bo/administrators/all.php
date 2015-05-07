<div class="btn-toolbar list-toolbar">
    <a class="btn btn-primary" href="bo/administrators/add"><i class="fa fa-plus"></i> Nouvel administrateur</a>
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
			<th>Prénom</th>
			<th>Nom</th>
			<th>Email</th>
			<th style="width: 3.5em;"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($administrators as $administrator): ?>
			<tr>
				<td><?php echo $administrator->id ?></td>
				<td><?php echo $administrator->login ?></td>
				<td><?php echo $administrator->forname ?></td>
				<td><?php echo $administrator->name ?></td>
				<td><?php echo $administrator->email ?></td>
				<td>
					<a href="<?php echo base_url('bo/administrators/edit/'.$administrator->id) ?>"><i class="fa fa-pencil"></i></a>
					<a href="#" class="confirm" 
					   data-url="<?php echo base_url('bo/administrators/delete/'.$administrator->id) ?>" 
					   data-header="Suppression d'un administrateur" 
					   data-body="<p>Attention!</p><p>Vous êtes sur le point de supprimer un administrateur.</p><p>Continuer?</p> ">
						<i class="fa fa-trash-o"></i>
					</a>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
<?php echo pagination($max, 'bo/users/all', $start, $offset); ?>


