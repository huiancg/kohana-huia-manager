<!DOCTYPE html>
<html class="huia-manager controller-<?php echo $controller; ?> action-<?php echo $action; ?>">
<head>
	<title><?php echo isset($title) ? __($title) . ' - Manager' : 'Manager'; ?></title>       
	<link rel="stylesheet" href="<?php echo $bootstrap_css; ?>">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker3.min.css">
	<base href="<?php echo Kohana::$base_url; ?>" />
	<!-- Script -->		
	<script type="text/javascript">
		var base_url = '<?php echo Kohana::$base_url; ?>' ;
	</script>
</head>
<body>
	<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="//cdn.ckeditor.com/4.4.7/standard/ckeditor.js"></script>
	
	<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/locales/bootstrap-datepicker.pt-BR.min.js" charset="UTF-8"></script>
	<script>
	</script>
	<script>
		$('body').on('click', '.btn-delete', function(){
			if(!confirm('Tem certeza que deseja excluir?'))
				return false;
		});
		$('body').on('change', '.filter-submit', function(){
			$(this).parents('form').submit();
		});
	</script>

	<nav class="navbar navbar-default ng-scope" role="navigation">
		<div class="container">
			<div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <a class="navbar-brand " href="<?php echo Kohana::$base_url; ?>manager/">Manager</a>
			</div>
			
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
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
		
		<?php echo View::factory('template/manager/footer')->render(); ?>
	</section>

	<?php echo View::factory($scripts); ?>
</body>
</html>
