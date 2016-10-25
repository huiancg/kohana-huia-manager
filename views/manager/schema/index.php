<h2><?php echo $title; ?></h2>
<hr />
<table class="table">
	<thead>
		<tr>
			<th><?php echo __('Model'); ?></td>
			<th><?php echo __('Belongs To'); ?></th>
			<th><?php echo __('Through'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($models as $model) : ?>
		<tr>
			<th><?php echo $model['model']; ?></th>
			<td>
				<?php foreach ($model['belongs_to'] as $item) : ?>
					<a href="#" class="btn btn-sm btn-warning delete-relation" data-type="belongs_to" data-table_name="<?php echo $model['table_name'] ?>" data-foreign_key="<?php echo $item['foreign_key'] ?>">
						<?php echo $item['model']; ?> <i class="glyphicon glyphicon-trash"></i>
					</a>
				<?php endforeach; ?>
				
				<a href="#" data-toggle="modal" data-target="#modal-relation" class="btn btn-sm btn-success" data-type="belongs_to" data-table_name="<?php echo $model['table_name'] ?>" data-foreign_key="<?php echo '' ?>"><i class="glyphicon glyphicon-plus"></i></a>
			</td>
			<td>
				<?php foreach ($model['through'] as $item) : ?>
					<a href="#" class="btn btn-sm btn-warning delete-relation" data-type="through" data-table_name="<?php echo $item['through']; ?>">
						<?php echo $item['model']; ?> <i class="glyphicon glyphicon-trash"></i>
					</a>
				<?php endforeach; ?>
				
				<a href="#" data-toggle="modal" data-target="#modal-relation" class="btn btn-sm btn-success" data-type="through" data-table_name="<?php echo $model['table_name']; ?>"><i class="glyphicon glyphicon-plus"></i></a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-relation">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="model" class="control-label">Table:</label>
            <input type="text" class="form-control" id="modal-table" disabled>
          </div>
          <div class="form-group">
            <label for="model" class="control-label">Model:</label>
            <select class="form-control" id="modal-model" required>
            	<?php foreach ($models as $model) : ?>
            		<option value="<?php echo $model['table_name']; ?>"><?php echo $model['model']; ?></option>
            	<?php endforeach; ?>
            </select>
          </div>
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
        <button type="button" class="btn btn-primary" id="modal-save"><?php echo __('Save'); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
	$('a.delete-relation').click(function(e) {
		e.preventDefault();
		
		// confirm
		if ( ! confirm('<?php echo __('Are you sure?'); ?>'))
		{
			return;
		}

		var $this = $(this);
		
		$.post(base_url + 'manager/schema/delete_table', $this.data()).then(function(data) {
			console.info(data);
			if (data && data.executed) {
				window.location.reload();
			} else {
				alert('Erro');
			}
		});
	});

	$('#modal-relation').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget);
	  var type = button.data('type');
	  var table_name = button.data('table_name');

	  var modal = $(this);

	  modal.find('.modal-title').text('<?php echo __('Add'); ?>' + ' ' + type);
	  
	  var modal_table = modal.find('#modal-table');
	  modal_table.val(table_name);

	  var modal_model = modal.find('#modal-model');
	  
	  modal_model.val('');

	  var options = modal_model.find('option').show();
	  options.filter(function() {
	  	return $(this).val() === table_name;
	  }).hide();

	  modal_model.off('change').on('change', function() {
	  	if (type === 'through')
	  	{
	  		modal_table.val(table_name + '_' + modal_model.val());
	  	}
	  });

	  modal.find('#modal-save').off('click').on('click', function(e) {
	  	e.preventDefault();
	  	
	  	if ( ! modal_model.val()) {
	  		modal_model.focus();
	  		return;
	  	}

	  	var data = {
	  		type: type,
	  		model: modal_model.val(),
	  		table: modal_table.val(),
	  		table_name: table_name
	  	};

	  	$.post(base_url + 'manager/schema/upset_table', data).then(function(data) {
	  		// modal.modal('hide');
	  		console.info(data);
	  		// window.location.reload();
	  	});
	  });
	})
</script>