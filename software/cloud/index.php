<?php 
require_once('../inc/fw.php');
$page="template/main.html";

if (isset($_GET["logout"])){
    $fw->logout();
    header('Location: ?');
    exit;
}

if ( isset($_POST["email"]) && isset($_POST["pwd"]) &&
      ($_POST["email"]!="") && ($_POST["pwd"]!="") )
{
    $_SESSION["user"] = $fw->login( $_POST["email"], $_POST["pwd"] );
    
    if ($_SESSION["user"] == false)
    {
        $err="Acces Refuser";
        unset($_SESSION["user"]);
    }
}

if (!$fw->login())
{
    $page = "template/login.html";
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="css/signin.css">
  </head>
  <body>
  	
  	<div class="container">
	<?php
		require_once($page);
	?>
	</div> <!-- /container -->

    <!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.md5.js"></script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>

    <!-- App -->
    <script type="text/javascript">
      $("#login").click(function(){
        if ($("#inputPassword").val())
        {
          var md5 = $.md5($("#inputPassword").val());
          $("#pwd").val(md5);
          $("#inputPassword").val("");
          $("#inputPassword").prop('required',false);
        }
      });
    </script>
  </body>
</html>