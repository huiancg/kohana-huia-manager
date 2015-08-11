<h2><?php echo __(Inflector::plural($title)); ?></h2>
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
			<tbody id="form-rows" data-model-name="<?php echo $model_name; ?>">
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
					
					<td>
					
					<?php if (in_array($name, $upload_fields)) : ?>

						<a target="_blank" href="<?php echo $row->get_url($name); ?>">Download</a>
					
					<?php elseif (in_array($name, $image_fields)) : ?>
					
						<img width="50" src="<?php echo $row->get_url($name); ?>" class="img-rounded">

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
						<a href="<?php echo URL::site('manager/' . $name . '/edit/' . $row->{$name}->id); ?>"><?php echo $row->{$name}->{$column_name}; ?></a>
					
					<?php elseif (in_array($name, $boolean_fields)) : ?>

						<a href="javascript:;" 
							 data-id="<?php echo $row->id; ?>" 
							 data-field="<?php echo $name; ?>" 
							 data-status="<?php echo $row->$name; ?>" 
							 data-status-no="<?php echo $boolean_fields_labels[$name][0]; ?>" 
							 data-status-yes="<?php echo $boolean_fields_labels[$name][1]; ?>" 
							 class="bool-field btn btn-<?php echo ($row->$name) ? 'success' : 'danger'; ?>">
							<?php echo $boolean_fields_labels[$name][$row->$name]; ?>
						</a>

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

<script>
	var $form_rows = $('#form-rows');
	var model_name = $form_rows.data('model-name');
	
	$(document).on('click', '.bool-field', function(e) {
		e.preventDefault();
		var $this = $(this);
		if ($this.data('running')) {
			return;
		}
		$this.data('running', true);
		var data = {};
		var actived = ($this.data('status')) ? 0 : 1;
		data[$this.data('field')] = actived;
		var url = base_url + 'manager/' + model_name + '/edit/'+ $this.data('id');
		var btn_class = $this.attr('class');
		var btn_text = $this.text();
		$this.attr('class', 'btn').html('<i class="glyphicon glyphicon-refresh">');
		$.post(url, data, function(r) {
			if (r.errors) {
				var message = '';
				for (var i in r.errors) {
					message += r.errors[i] + "\n";
				}
				alert(message);
			} else {
				btn_class = (actived) ? 'btn btn-success' : 'btn btn-danger';
				btn_text = (actived) ? $this.data('status-yes') : $this.data('status-no');
			}
			$this.attr('class', 'bool-field ' + btn_class).text(btn_text);
			$this.data('running', false);
		});
	});
</script>