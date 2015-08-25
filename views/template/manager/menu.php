<ul class="nav navbar-nav">
	<?php
	$model_classes = ORM_Autogen::get_models();
	foreach ($model_classes as $model_class)
	{
		?>
		<li>
			<a href="./manager/<?php echo $model_class; ?>" target="_self" class="ng-binding"><?php echo __(Inflector::plural($model_class)); ?></a>
		</li>
		<?php
	}
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