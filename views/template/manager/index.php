<h2><?php echo $title ?></h2>
<hr />

<?php echo View::factory($breadcrumb) ?>

<div class="row">
	<div class="col-md-12">
	  <a href="<?php echo $url; ?>/new" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>Novo</a>
	</div>
</div>

<hr />
<div class="row">
	<div class="col-md-12">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th data-name="id">#</th>
					<?php foreach ($labels as $name => $description) : ?>
						<?php if (in_array($name, $ignore_fields)) { continue; } ?>
						<th data-name="<?php echo $name; ?>"><?php echo $description; ?></th>
					<?php endforeach; ?>
					<th style="min-width: 200px;">Ações</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! count($rows)) : ?>
					<th class="info" colspan="<?php echo (count($model->labels()) + 2); ?>">
						<center>Sem itens cadastrados</center>
					</th>
				</tr>
			<?php endif; ?>
			<?php foreach($rows as $row) : ?>
				<tr data-id="<?php echo $row->id; ?>">
					<td><?php echo $row->id; ?></td>
					<?php foreach ($labels as $name => $description) : ?>
					
					<?php if (in_array($name, $ignore_fields)) { continue; } ?>
					
					<?php if (in_array($name, $image_fields)) : ?>
					
					<td><img width="50" src="<?php echo $row->get_image_url($name); ?>" class="img-rounded"></td>

					<?php elseif (in_array($name, $boolean_fields)) : ?>
						<td><?php echo $boolean_fields_labels[$name][$row->$name]; ?></td>
					<?php else : ?>
					
					<td><?php echo Text::limit_chars(strip_tags($row->$name), 20); ?></td>
					
					<?php endif; ?>
					
					<?php endforeach; ?> 

					<?php echo View::factory($form_actions, $row->as_array()); ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>