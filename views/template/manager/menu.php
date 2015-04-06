<ul class="nav navbar-nav">
	<?php
	/*
	<li>
		<a href="./manager/tag" target="_self" class="ng-binding">Social Gifts</a>
	</li>
	*/
	$dir = 'classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
	foreach ((Kohana::list_files($dir)) as $file => $path)
	{
		if (is_string($path) AND strpos($path, APPPATH) !== FALSE)
		{
			$menu_model = str_replace(array($dir, APPPATH, EXT), '', $path);
			?>
			<li>
				<a href="./manager/<?php echo strtolower($menu_model); ?>" target="_self" class="ng-binding"><?php echo __($menu_model); ?></a>
			</li>
			<?php
		}
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