<h2><?php echo $title; ?></h2>
<hr />

<?php

echo Form::open();

echo '<table class="table" id="table-fields">';

echo '<thead>';
  echo '<tr>';
    echo '<th>'.__('Field').'</th>';
    echo '<th>'.__('Type').'</th>';
    echo '<th>'.__('Required').'</th>';
    echo '<th>'.__('Actions').'</th>';
  echo '</tr>';
echo '</thead>';

echo '<tbody>';
foreach ($fields as $field)
{
  echo '<tr>';

    echo '<td>';
      echo Form::input($field['field'], $field['label'], ['class' => 'form-control']);
    echo '</td>';

    echo '<td>';
      echo Form::select($field['field'], $field['types'], array_search($field['type'], $field['types']), $field['attributes']);
    echo '</td>';

    echo '<td>';
      echo '<div class="checkbox checkbox-required">';
        echo '<center>';
          echo Form::checkbox($field['field'], NULL, $field['required']);
        echo '</center>';
      echo '</div>';
    echo '</td>';

    echo '<td>';
      echo '<a href="#" class="btn btn-success disabled save-field" data-field="'.$field['field'].'"><i class="glyphicon glyphicon-floppy-disk"></i></a> ';
      echo '<a href="#" class="btn btn-danger delete-field" data-field="'.$field['field'].'"><i class="glyphicon glyphicon-remove"></i></a>';
    echo '</td>';
  echo '</tr>';
}
echo '</tbody>';

echo '</table>';

echo '<div class="form-group">';
  echo '<div class="btn-group btn-group-justified" role="group" aria-label="...">';
    echo '<a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-field"><i class="glyphicon glyphicon-plus"></i> '.__('Add').'</a>';
  echo '</div>';
echo '</div>';

echo Form::close();

?>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-field">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <form>

          <div id="modal-errors" class="alert alert-danger hide" role="alert"></div>

          <div class="form-group">
            <label for="model-name" class="control-label"><?php echo __('Name'); ?>:</label>
            <input type="text" class="form-control" id="modal-name">
          </div>
          <div class="form-group">
            <label for="modal-type" class="control-label"><?php echo __('Type'); ?>:</label>
            <?php echo Form::select('model-type', $field_types, 0, ['class' => 'form-control', 'id' => 'modal-type']); ?>
          </div>
          <div class="form-group">
            <label for="modal-type" class="control-label"><?php echo __('Options'); ?>:</label>
            
            <div class="checkbox">
              <label>
                <input type="checkbox" name="required" id="model-required"> <?php echo __('Required'); ?>
              </label>
            </div>

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
  
  var modal_field = $('#modal-field');

  var refresh = function(token, reload) {
    $.post(base_url + 'manager/schema', {token: token}).then(function() {
      if (reload === undefined || reload) {
        window.location.reload();
      }
    });
  }

  modal_field.on('show.bs.modal', function (event) {
    modal_field.find('form')[0].reset();

    $('#modal-errors').addClass('hide');

    $('.modal-body form').off('submit').on('submit', function(e) {
      e.preventDefault();

      $('#modal-save').click();
    });

    var running = false;

    $('#modal-save').off('click').on('click', function(e) {
      e.preventDefault();

      if (running) {
        return;
      }

      running = true;

      var name = modal_field.find('#modal-name').val();
      var model_type = modal_field.find('#modal-type');
      var type = model_type.find('option').eq(model_type.val()).text();
      var required = modal_field.find('#model-required').is(':checked');

      if ( ! name) {
        modal_field.find('#modal-name').focus();
        running = false;
        return;
      }

      var data = {
        table_name: '<?php echo $model->table_name() ?>',
        name: name,
        type: type,
        required: required,
        last: ($('#table-fields tbody tr:last').find('input').attr('name') || 'id')
      };

      $.post(base_url + 'manager/schema/alter_table', data).then(function(data) {
        if ( ! data || data.error) {
          $('#modal-errors').text(data.error || 'Erro').removeClass('hide');
        } else {
          refresh(data.token);
        }
        running = false;
      });
    });
  });

  $('input, select', '#table-fields').on('change keyup keypress', function() {
    $(this).closest('tr').addClass('warning');
    $(this).closest('tr').find('.save-field').removeClass('disabled');
  });

  $('.save-field').on('click', function(e) {
    e.preventDefault();

    if ( ! confirm('<?php echo __('Are you sure?'); ?>'))
    {
      return;
    }

    var btn = $(this);
    var field = btn.data('field');
    var field_to = btn.closest('tr').find('input:first').val();
    var model_type = btn.closest('tr').find('select:first');
    var type = model_type.find('option').eq(model_type.val()).text();

    var data = {
      table_name: '<?php echo $model->table_name() ?>',
      from: field,
      to: field_to,
      required: btn.closest('tr').find('.checkbox-required input').is(':checked'),
      type: type
    };

    $.post(base_url + 'manager/schema/update_field', data).then(function(data) {
      btn.closest('tr').removeClass('warning').find('.save-field').addClass('disabled');
      refresh(data.token, false);

      console.info(data);
    });
  });

  $('.delete-field').on('click', function(e) {
    e.preventDefault();

    if ( ! confirm('<?php echo __('Are you sure?'); ?>'))
    {
      return;
    }

    var btn = $(this);
    var field = btn.data('field');

    var data = {
      table_name: '<?php echo $model->table_name() ?>',
      field: field
    };

    $.post(base_url + 'manager/schema/delete_field', data).then(function(data) {

      btn.closest('tr').addClass('danger').fadeOut('fast', function() {
        $(this).remove();
        console.info($(this));
      });
      
      refresh(data.token, false);
    });
  });

</script>