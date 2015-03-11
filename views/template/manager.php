<!DOCTYPE html>
<html class="controller-<?php echo $controller; ?> action-<?php echo $action; ?>">
<head>
	<title><?php echo isset($title) ? $title . ' - Manager' : 'Manager'; ?></title>       
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<base href="<?php echo Kohana::$base_url; ?>" />
	<!-- Script -->		
	<script type="text/javascript">
		var base_url = '<?php echo Kohana::$base_url; ?>' ;
	</script>
</head>
<body>
	<nav class="navbar navbar-default ng-scope" role="navigation">
		<div class="container">
			<div class="navbar-header">					
				<a class="navbar-brand " href="<?php echo Kohana::$base_url; ?>manager/">Manager</a>
			</div>
			<div class="collapse navbar-collapse">
				<?php echo View::factory('template/manager/menu'); ?>
			</div><!--/.nav-collapse -->
		</div>
	</nav>
	<section class="container">			
		<?php if(isset($success) && $success) { ?>
		<div class="alert alert-success">
			<?php echo $success ?>
		</div>
		<?php } ?>
		<?php if(isset($errors)) { ?>
		<div class="alert alert-danger">
			<ul>
				<?php foreach($errors as $error) { ?>
				<li><?php echo $error ?></li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
		<?php echo $content ?>
	</section>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script>
		$('body').on('click', '.btn-delete', function(){
			if(!confirm('Tem certeza que deseja excluir?'))
				return false;
		});
		$('body').on('change', '.filter-submit', function(){
			$(this).parents('form').submit();
		});
	</script>
	<?php echo View::factory($scripts); ?>
</body>
</html>