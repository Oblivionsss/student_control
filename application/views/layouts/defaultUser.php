<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>
	<script src="/public/script/jquery.js"></script>
	
	<link href="/public/style/reset.css" rel="stylesheet"  type="text/css" media="screen" />	
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link href="/public/style/user.css" rel="stylesheet"  type="text/css" media="screen" />	
    <link href="/public/style/styles.css" rel="stylesheet"  type="text/css" media="screen" />	
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    
    <script src="/public/script/form.js"></script>
</head>
<body>

<div class="containers">
    
    <!-- Верхняя часть страницы -->
    <header class ="clearfix" >
        <div class="logo">
            <i class="fa fa-user" aria-hidden="true"></i>
        </div>

        <div class="user">
            <?php echo $user[0]['Name'] . " " . $user[0]['Matern']?>
        </div>
        
        <div class="logout">
            <a class="btn btn-primary" href="/application/core/Logout.php">
                Выход
            </a>
         
        </div>
    </header>

    <!-- Основная часть страницы -->
    <?php echo $content; ?>
    
    <!-- Футер -->
    <div class="footer">
        <div>
            <a href="https://www.mirea.ru/">РОССИЙСКИЙ ТЕХНОЛОГИЧЕСКИЙ УНИВЕРСИТЕТ</a>
        </div>
	</div>
</div>

</body>
</html>