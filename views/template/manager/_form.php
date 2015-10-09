<h2><?php echo __($title); ?></h2>
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

		<?php elseif (in_array($name, $date_fields)) : ?>

		<?php echo Form::input($name, $model->$name, array('class' => 'form-control')) ?><br />

		<script>
			<?php 
			$language = explode('-', I18n::lang());
			$language = $language[0] . (isset($language[1]) ? '-' . strtoupper($language[1]) : '');
			?>
			jQuery('input[name="<?php echo $name ?>"]').datepicker({
				language: '<?php echo $language; ?>',
				todayBtn: true,
				format: 'yyyy-mm-dd'
			});
		</script

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
			<br /><img class="img-responsive" src="<?php echo $model->get_url($name); ?>">
		<?php endif; ?>

		<?php elseif ( ! empty($belongs_to) AND Arr::get($belongs_to, str_replace('_id', '', $name))) : ?>
		
		<?php
		$column_name = NULL;
		
		$model_name = ORM::get_model_name($name);
		
		if ( ! class_exists($model_name))
		{
			$model_name = Arr::path($belongs_to, $name.'.model');
		}

		$parent_belongs = Model_App::factory($model_name);

		foreach ($parent_belongs->list_columns() as $column => $values)
		{
			if (Arr::get($values, 'type') === 'string' AND $column_name === NULL)
			{
				$column_name = $column;
			}
		}

		$belongs_to_values = Arr::merge(array('' => 'Selecione'), $parent_belongs->find_all()->as_array('id', $column_name));
		$select_name = Arr::path($model->belongs_to(), $name.'.foreign_key');
		?>
		<?php echo Form::select($select_name, $belongs_to_values, $model->{$select_name}, array('class' => 'form-control')) ?>
		
		<?php elseif ( ! empty($has_many) AND Arr::get($has_many, $name) AND Arr::path($has_many, $name.'.through')) : ?>

		<?php
		$column_name = NULL;

		$model_name = ORM::get_model_name($name);

		$far_primary_key = Arr::path($has_many, $name . '.far_primary_key', 'id');
		
		if ( ! class_exists($model_name))
		{
			$model_name = Arr::path($has_many, $name.'.model');
		}

		$parent_has_many = Model_App::factory($model_name);
		
		foreach ($parent_has_many->list_columns() as $column => $values)
		{
			if (Arr::get($values, 'type') === 'string' AND $column_name === NULL)
			{
				$column_name = $column;
			}
		}

		$selects = $parent_has_many->find_all()->as_array('id', $column_name);
		$selected = $model->$name->find_all()->as_array(NULL, $far_primary_key);

		echo '<div style="max-height: 400px; overflow-x: auto; border: 1px solid #ddd; border-radius: 3px; padding: 0 10px;">';
		foreach ($selects as $select_id => $select_name)
		{
			echo '<div class="checkbox">';
				echo '<label class="checkbox-inline">';
					echo Form::checkbox($name.'[]', $select_id, in_array($select_id, $selected)) . ' ' . $select_name . ' ';
				echo '</label>';
			echo '</div>';
		}
		echo '</div>';

		// echo Form::select($name.'[]', , , array('class' => 'form-control', 'multiple' => 'multiple')) ?>
		
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