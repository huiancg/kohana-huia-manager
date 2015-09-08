<?php

function render_menu($items, $parent = NULL)
{ 
  $html = '';
  $ignored = array();
  foreach ($items as $item)
  {
    if (in_array($item, $ignored))
    {
      continue;
    }
    
    $matches = preg_grep('/^'.$item.'_/i', $items);
    if ($matches AND ! $parent)
    {
      $html .= '<li><a href="#" class="dropdown-toggle" data-toggle="dropdown">';
        $html .= __(Inflector::plural($item)) .' <span class="caret"></span>';
      $html .= '</a>';
      $html .= '<ul class="dropdown-menu">';
        $html .= '<li><a href="./manager/'.strtolower($item).'">'.__(Inflector::plural($item)).'</a></li>';
        $html .= render_menu($matches, $item);
      $html .= '</ul></li>';
      
      $ignored = array_merge($ignored, $matches);
    }
    else
    {
      $prepend = '';
      if ($parent)
      {
        $underlines = count(explode('_', $item));
        if ($underlines >= 3)
        {
          for ($i = $underlines; $i > 3; $i--)
          {
            $prepend .= '<i class="glyphicon glyphicon-option-horizontal"></i>';
          }
          $prepend .= '<i class="glyphicon glyphicon-triangle-right"></i>';
        }
      }
      $html .= '<li><a href="./manager/'.strtolower($item).'">'.$prepend.__(Inflector::plural($item)).'</a></li>';
    }
  }
  return $html;
}

?>
<ul class="nav navbar-nav">
	<?php
	$model_classes = ORM_Autogen::get_models();
  sort($model_classes);
	echo render_menu($model_classes);
	?>
</ul>
<ul class="nav navbar-nav navbar-right">
	<li>
		<a href="./manager/user/edit/<?php echo Auth::instance()->get_user()->id; ?>">Logado como: <?php echo Auth::instance()->get_user()->username ?></a>
	</li>
	<li>
		<a href="./manager/login/logout">Sair</a>
	</li>
</ul>