<form class="form">
  <fieldset>
    <?php echo Form::input('q', Request::current()->query('q'), array('class' => 'col-md-8')); ?>
    <?php echo Form::button('', 'Buscar', array('class' => 'col-md-4')); ?>
  </fieldset>
</form>