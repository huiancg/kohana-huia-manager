<h2><?php echo $title ?></h2>
<hr />

<?php echo View::factory($breadcrumb) ?>

<form method="post" enctype="multipart/form-data">
	<?php foreach ($labels as $name => $description) : ?>
	<?php if (in_array($name, $ignore_fields)) { continue; } ?>
	
	<?php if ($parent AND $parent === $name) { continue; } ?>
	
	<div class="form-group">
		<?php echo Form::label($name, $description) ?>

		<?php if (preg_match('/password/i', $name)) : ?>

		<?php echo Form::password($name, NULL, array('class' => 'form-control')) ?>

		<?php elseif (in_array($name, $text_fields)) : ?>
		
		<?php echo Form::textarea($name, $model->$name, array('class' => 'form-control')) ?>

		<script>
        CKEDITOR.replace('<?php echo $name ?>', {
      			toolbar: [
							{ name: 'basicstyles', items: [ 'Bold', 'Italic' ] },
							{ name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
							{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] }, 
							{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] }
						]
      	});
    </script>

		<?php elseif (in_array($name, $upload_fields)) : ?>
		
		<?php if ($model->$name) : ?>
			- <strong><a target="_blank" href="<?php echo $model->get_url($name); ?>">Download</a></strong>
		<?php endif; ?>
		
		<?php echo Form::file($name, array('class' => 'form-control')) ?>

		<?php elseif (in_array($name, $image_fields)) : ?>
		
		<?php echo Form::file($name, array('class' => 'form-control')) ?>
		
		<?php if ($model->$name) : ?>
			<br /><img src="<?php echo $model->get_url($name); ?>">
		<?php endif; ?>

		<?php elseif ( ! empty($belongs_to) AND Arr::get($belongs_to, str_replace('_id', '', $name))) : ?>
		
		<?php
		$column_name = NULL;
		$parent_belongs = Model_App::factory(ucfirst($name));
		foreach ($parent_belongs->list_columns() as $column => $values)
		{
			if (Arr::get($values, 'type') === 'string' AND $column_name === NULL)
			{
				$column_name = $column;
			}
		}
		$belongs_to_values = Arr::merge(array('' => 'Selecione'), $parent_belongs->find_all()->as_array('id', $column_name));
		?>
		<?php echo Form::select($name.'_id', $belongs_to_values, $model->$name, array('class' => 'form-control')) ?>
		
		<?php elseif ( ! empty($has_many) AND Arr::get($has_many, $name) AND Arr::path($has_many, $name.'.through')) : ?>

		<?php
		$column_name = NULL;
		$model_parent_name = ucfirst(Inflector::singular($name));
		$parent_belongs = Model_App::factory($model_parent_name);
		foreach ($parent_belongs->list_columns() as $column => $values)
		{
			if (Arr::get($values, 'type') === 'string' AND $column_name === NULL)
			{
				$column_name = $column;
			}
		}
		echo Form::select($name.'[]', Model_App::factory($model_parent_name)->find_all()->as_array('id', $column_name), $model->$name->find_all()->as_array('id'), array('class' => 'form-control', 'multiple' => 'multiple')) ?>
		
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