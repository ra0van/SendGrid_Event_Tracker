<?php
	include_once(".\module.php");
	session_start();
	if(isset($_POST['logout'])){
		if ($_POST['logout']=='1' && isset($_SESSION['userid'])) {
			session_unset();
			session_destroy();
			?>
				<script type="text/javascript">
					alert(logged out successfully);
				</script>
			<?php
			// die();
		}
	}
	
	if(isset($_SESSION['userid'])){
		if(isset($_GET['p'])){
			redirect_url($_GET['p']);
		}
		redirect_url('index.php');
	}
	else{
		if(isset($_POST['user']) && isset($_POST['passwd'])){
			$conn = getMongoDbConnection() or  die('Error connecting to db');
			$dbName = $conn->selectDB('test');
			$collection = $dbName->users;

			$query = array('_id'=>$_POST['user'],'password'=>$_POST['passwd']);

			$cursor = $collection->findOne($query);
			// echo "<pre>";
			// var_dump($cursor);
			// foreach ($cursor as $key => $value) {
			// 	print_r($value);
			// }
			if($cursor){
				// echo "set";
				$_SESSION['userid'] = $_POST['user'];
				if(isset($_GET['p']))
					redirect_url($_GET['p']);
				else{
					redirect_url('index.php');
				}
			}
			else{
			
		
			}
		}
		
		?>
		

		<?php
	}

	// function redirect_url($url,$status_code=303){
	// 	header("Location:".$url,true,$status_code);
	// 	die();
	// }
?>

<html>
	<head>
		<title>Email Reports</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
		<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	</head>
	<body>
		<center>

			<div class="container">    
                <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
                    <div class="panel panel-info" >
                        <div class="panel-heading">
                            <div class="panel-title">Sign In</div>
                            <!-- <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div> -->
                        </div>     

                        <div style="padding-top:30px" class="panel-body" >
                            <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                                <form id="loginform" class="form-horizontal" role="form" action="login.php" method="post">
                                            
                                    <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                        <input type="text" class="form-control" name="user" id="user" value="" placeholder="username">
                                    </div>
                                        
                                    <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                        <input type="password" class="form-control" name="passwd" id="passwd" placeholder="password">
                                    </div>
                                
                                   <!--  <div class="input-group">
                                      <div class="checkbox">
                                        <label>
                                          <input id="login-remember" type="checkbox" name="remember" value="1"> Remember me
                                        </label>
                                      </div>
                                    </div> -->

                                    <div style="margin-top:10px" class="form-group">
                                        <div class="col-sm-12 controls">
                                            <!-- <a id="btn-login" href="#" class="btn btn-success">Login  </a> -->
                                            <button id="btn-login" type="submit" class="btn btn-success"><i class="icon-hand-right"></i>Login</button>
                                        </div>
                                    </div>   
                                </form>     
                            </div>                     
                        </div>  
                </div> 
         </div>
		</center>
		
		
	</body>
</html>