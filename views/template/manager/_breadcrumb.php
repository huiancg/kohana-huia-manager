<ol class="breadcrumb">
  <li><a href="<?php echo Kohana::$base_url; ?>manager">Manager</a></li>

  <?php if ($parent) : ?>
  <li><a href="<?php echo Kohana::$base_url; ?>manager/<?php echo $parent; ?>/edit/<?php echo $parent_id; ?>"><?php echo __($parent_title); ?></a></li>
  <?php endif; ?>

  <?php foreach ($breadcrumbs as $name => $link) : ?>
  <li><a href="<?php echo $link; ?>"><?php echo __($name); ?></a></li>
  <?php endforeach; ?>

  <?php if ($model->id) : ?>
  <li><a href="<?php echo $url; ?>/index"><?php echo __(Inflector::plural($title)); ?></a></li>
  <li class="active"><?php echo ($model->id) ? 'Editar' : 'Criar' ?></li>
  <?php else : ?>
  <li><?php echo __(Inflector::plural($title)); ?></li>
  <?php endif; ?>
</ol>