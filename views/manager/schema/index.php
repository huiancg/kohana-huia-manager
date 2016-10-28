<h2><?php echo $title; ?></h2>
<hr />
<table class="table">
  
  <thead>
    <tr>
      <th><?php echo __('Model'); ?></td>
      <th><?php echo __('Belongs To'); ?></th>
      <th><?php echo __('Through'); ?></th>
      <th><?php echo __('Action'); ?></th>
    </tr>
  </thead>
  
  <tbody>
    <?php foreach ($models as $model) : ?>
    <tr>
      <th>
        <?php foreach ($model['parents'] as $parent) : ?>
          <span class="badge"><?php echo $parent; ?></span> 
        <?php endforeach; ?>
        <?php echo $model['name']; ?>
      </th>
      <td>
        <?php foreach ($model['belongs_to'] as $item) : ?>
          <a href="#" class="btn btn-sm btn-warning delete-relation" data-type="belongs_to" data-table_name="<?php echo $model['table_name'] ?>" data-foreign_key="<?php echo $item['foreign_key'] ?>">
            <?php echo $item['name']; ?> <i class="glyphicon glyphicon-trash"></i>
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
      <td>
        <a class="btn btn-sm btn-info rename-table" data-toggle="modal" data-target="#modal-rename-table" data-table_name="<?php echo $model['table_name']; ?>" href="#">
          <span class="glyphicon glyphicon-pencil"></span> <?php echo __('Rename'); ?>
        </a>
        <a class="btn btn-sm btn-primary" href="manager/schema/edit/0?model=<?php echo $model['model']; ?>">
          <span class="glyphicon glyphicon-edit"></span> <?php echo __('Edit'); ?>
        </a>
        <a class="btn btn-sm btn-danger delete-table" data-table_name="<?php echo $model['table_name']; ?>" href="#">
          <span class="glyphicon glyphicon-trash"></span>  <?php echo __('Delete'); ?>
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>

  <tfoot>
    <tr>
      <td colspan="4">
        <div class="form-group">
          <div class="btn-group btn-group-justified" role="group" aria-label="...">
            <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-new-table"><i class="glyphicon glyphicon-plus"></i> <?php echo __('Add'); ?></a>
          </div>
        </div>
      </td>
    </tr>
  </tfoot>
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

<div class="modal fade" tabindex="-1" role="dialog" id="modal-new-table">
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
            <input type="text" class="form-control" id="modal-table_name">
            <p class="text-info">
              <?php echo __('Use plural lowcase names in english without special characters.'); ?>
            </p>
          </div>
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
        <button type="button" class="btn btn-primary" id="modal-new-table-save"><?php echo __('Save'); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="modal-rename-table">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <form>
          
          <div class="form-group">
            <label for="model" class="control-label">From Table:</label>
            <input type="text" class="form-control" id="modal-rename-table_name" disabled>
          </div>

          <div class="form-group">
            <label for="model" class="control-label">To Table:</label>
            <input type="text" class="form-control" id="modal-rename-table_name_to">
            <p class="text-info">
              <?php echo __('Use plural lowcase names in english without special characters.'); ?>
            </p>
          </div>

        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
        <button type="button" class="btn btn-primary" id="modal-new-table-save"><?php echo __('Save'); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">

  var refresh = function(token, reload) {
    $.post(base_url + 'manager/schema', {token: token}).then(function() {
      if (reload === undefined || reload) {
        window.location.reload();
      }
    });
  }

  $('a.delete-relation').click(function(e) {
    e.preventDefault();
    
    // confirm
    if ( ! confirm('<?php echo __('Are you sure?'); ?>'))
    {
      return;
    }

    var $this = $(this);
    
    $.post(base_url + 'manager/schema/delete_relation', $this.data()).then(function(data) {
      console.info(data);
      if (data && data.executed) {
        refresh(data.token);
      } else {
        alert('Erro');
      }
    });
  });

  $('#modal-rename-table').on('show.bs.modal', function (event) {
    var modal = $(this);
    var button = $(event.relatedTarget);
    var table_name = modal.find('#modal-rename-table_name');
    var table_name_to = modal.find('#modal-rename-table_name_to');

    table_name.val(button.data('table_name'));
    table_name_to.focus().val(button.data('table_name'));

    modal.data('running', false);

    modal.find('#modal-new-table-save').off('click').on('click', function(e) {
      e.preventDefault();

      if (modal.data('running'))
      {
        return;
      }

      modal.data('running', true);

      var data = {
        table_name: table_name.val(),
        table_name_to: table_name_to.val()
      };

      $.post(base_url + 'manager/schema/rename_table', data).then(function(data) {
        
        if ( ! data || data.error) {
          alert(data.error);
        } else {
          modal.modal('hide');
          refresh(data.token);
        }

        modal.data('running', false);
      });
    });

  });

  $('#modal-new-table').on('show.bs.modal', function (event) {
    $('#modal-new-table-save').off('click').on('click', function(e) {
      e.preventDefault();

      var table_name = $('#modal-table_name');
      var isValidName = (new RegExp("^[a-z_]+s$")).test(table_name.val());
      var modal = $(this);

      if (modal.data('running')) {
        return;
      }
      modal.data('running', true);

      if ( ! table_name.val() || ! isValidName) {
        table_name.focus();
        modal.data('running', false);
        return;
      }

      var data = {
        table_name: table_name.val()
      };

      $.post(base_url + 'manager/schema/create_table', data).then(function(data) {
        modal.modal('hide');
        
        refresh(data.token);

        modal.data('running', false);
      });
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
        modal.modal('hide');
        
        refresh(data.token);
        running = false;
      });
    });
  });

  $('.delete-table').off('click').on('click', function(e) {
    e.preventDefault();

    // confirm
    if ( ! confirm('<?php echo __('Are you sure?'); ?>'))
    {
      return;
    }

    $.post(base_url + 'manager/schema/delete_table', {table_name: $(this).data('table_name')}).then(function(data) {
      
      if (data && data.token) {
        refresh(data.token);
      } else {
        alert('Erro');
      }

    });
  });
</script>