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
					
					<td>
					<?php if (in_array($name, $ignore_fields)) { continue; } ?>
					
					<?php if (in_array($name, $image_fields)) : ?>
					
						<img width="50" src="<?php echo $row->get_image_url($name); ?>" class="img-rounded">

					<?php elseif (Arr::get($has_many, $name)) : ?>

						<?php foreach ($row->{$name}->find_all() as $item) : ?>
							<?php $item = $item->as_array(); ?>
							<?php echo Arr::get($item, 'name', Arr::get($item, 'title'), Arr::get($item, 'description'), Arr::get($item, 'id')); ?>, 
						<?php endforeach; ?>
					
					<?php elseif (Arr::get($belongs_to, $name)) : ?>
						
						<?php
						$column_name = NULL;
						foreach ($row->{$name}->list_columns() as $column => $values)
						{
							if (Arr::get($values, 'type') === 'string' AND $column_name === NULL)
							{
								$column_name = $column;
							}
						}
						?>
						<?php echo $row->{$name}->{$column_name}; ?>
					
					<?php elseif (in_array($name, $boolean_fields)) : ?>

						<?php echo $boolean_fields_labels[$name][$row->$name]; ?>

					<?php else : ?>
					
						<?php echo Text::limit_chars(strip_tags($row->$name), 20); ?>
					
					<?php endif; ?>
					</td>

					<?php endforeach; ?> 

					<?php echo View::factory($form_actions, $row->as_array()); ?>

				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>