<ul class="nav navbar-nav">
	<?php /*
	<li>
		<a href="./manager/tag" target="_self" class="ng-binding">Social Gifts</a>
	</li>
	*/ ?>
</ul>
<ul class="nav navbar-nav navbar-right">
	<li>
		<a href="./manager/user/edit/<?php echo Auth::instance()->get_user()->id; ?>">Logado como: <?php echo Auth::instance()->get_user()->username ?></a>
	</li>
	<li>
		<a href="./manager/login/logout">Sair</a>
	</li>
</ul>