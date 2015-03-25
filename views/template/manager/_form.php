<h2><?php echo $title ?></h2>
<hr />

<?php echo View::factory($breadcrumb) ?>

<form method="post" enctype="multipart/form-data">
	<?php foreach ($labels as $name => $description) : ?>
	<?php if (in_array($name, $ignore_fields)) { continue; } ?>
	<div class="form-group">
		<?php echo Form::label($name, $description) ?>

		<?php if (preg_match('/password/i', $name)) : ?>

		<?php echo Form::password($name, NULL, array('class' => 'form-control')) ?>

		<?php elseif (in_array($name, $image_fields)) : ?>
		
		<?php echo Form::file($name, array('class' => 'form-control')) ?>
		
		<?php if ($model->$name) : ?>
			<br /><img src="<?php echo $model->get_image_url($name); ?>">
		<?php endif; ?>

		<?php elseif ( ! empty($belongs_to) AND Arr::get($belongs_to, str_replace('_id', '', $name))) : ?>

		<?php echo Form::select($name, Arr::merge(array('' => 'Selecione'), Model_App::factory(str_replace('_id', '', $name))->find_all()->as_array('id', 'name')), $model->$name, array('class' => 'form-control')) ?>

		<?php elseif ( ! empty($has_many) AND Arr::get($has_many, $name) AND Arr::path($has_many, $name.'.through')) : ?>

		<?php echo Form::select($name.'[]', Model_App::factory(ucfirst(Inflector::singular($name)))->find_all()->as_array('id', 'name'), $model->$name->find_all()->as_array('id'), array('class' => 'form-control', 'multiple' => 'multiple')) ?>
		
		<?php elseif (in_array($name, $boolean_fields)) : ?>			
			<div class="radio form-control">				
				<label><?php echo Form::radio($name, 1, (bool) $model->$name) . $boolean_fields_labels[$name][1] ?></label>				
				<label><?php echo Form::radio($name, 0, ! $model->$name) . $boolean_fields_labels[$name][0] ?></label>				
			</div>
		<?php else : ?>
		
		<?php echo Form::input($name, $model->$name, array('class' => 'form-control')) ?>
		
		<?php endif; ?>
	</div>
	<?php endforeach; ?>

	<div class="form-group">
		<button class="btn btn-success btn-lg btn-block" type="submit">Salvar</button>
	</div>
</form>