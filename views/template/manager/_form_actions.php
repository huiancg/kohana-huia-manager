<td>
	<?php foreach ($model->has_many() as $name => $values) : ?>
	<?php if (Arr::get($values, 'through') OR in_array($name, $ignore_actions)) { continue; } ?>
	<a class="btn btn-sm btn-success" href="<?php echo $url; ?>/<?php echo $id ?>/<?php echo str_replace('_id', '', Arr::get($values, 'far_key')); ?>">
		<span class="glyphicon glyphicon-edit"></span> <?php echo Inflector::plural(Arr::get($values, 'model')); ?>
	</a>
	<?php endforeach; ?>
	<?php foreach ($actions as $data) : ?>
  <a class="btn btn-sm btn-<?php echo Arr::get($data, 'btn', 'success'); ?>" href="<?php echo __(Arr::get($data, 'link', ''), array(':id' => $id)); ?>">
		<span class="glyphicon glyphicon-<?php echo Arr::get($data, 'icon', 'edit'); ?>"></span> <?php echo Arr::get($data, 'text', ''); ?>
	</a>
  <?php endforeach; ?>

	<a class="btn btn-sm btn-primary" href="<?php echo $url; ?>/edit/<?php echo $id ?>">
		<span class="glyphicon glyphicon-edit"></span> Editar
	</a>
	<a class="btn btn-sm btn-danger btn-delete" href="<?php echo $url; ?>/delete/<?php echo $id ?>">
		<span class="glyphicon glyphicon-trash"></span>  Excluir
	</a>
</td>