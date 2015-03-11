<!DOCTYPE html>
<html>
	<head>
        <title>Manager</title>       
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
		<!-- Script -->		
		<script type="text/javascript">
        	var rootUrl = '<?php echo Kohana::$base_url; ?>manager/' ;
        </script>
	</head>
	<body>
		<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">       
			<form method="post" action="">
				<fieldset>
					<h2>Acesso Restrito</h2>
					<hr />
					<?php if (isset($error)) { ?>
						<div ng-show="login_error" class="form-group">
							<div class="alert alert-danger"><?php echo $error ?></div>
						</div>
					<?php } ?>
					<div class="form-group">
						<input type="text" required="" placeholder="Usuário" class="form-control input-lg" name="username">
					</div>						
					<div class="form-group">
						<input type="password" required="" placeholder="Senha" class="form-control input-lg" name="password">
					</div>
					<hr />
					<div class="row">
						<div class="col-md-12">
							<input type="submit" value="Entrar" class="btn btn-lg btn-success btn-block">
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	</body>
</html>