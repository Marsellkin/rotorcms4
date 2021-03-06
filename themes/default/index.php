<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title>%TITLE%</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="image_src" href="/images/img/icon.png" />
	<?= include_style() ?>
	<link rel="stylesheet" href="/themes/default/css/style.css" type="text/css" />
	<link rel="alternate" href="/news/rss.php" title="RSS News" type="application/rss+xml" />
	<?= include_javascript() ?>
	<meta name="keywords" content="%KEYWORDS%" />
	<meta name="description" content="%DESCRIPTION%" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta name="generator" content="RotorCMS <?= $config['rotorversion'] ?>" />
</head>
<body>

<div class="cs" id="up">
	<!-- <a href="/"><span class="logotype"><?= $config['title'] ?></span></a><br /> -->
	<a href="/"><img src="<?= $config['logotip'] ?>" alt="<?= $config['title'] ?>" /></a><br />
	<?= $config['logos'] ?>
</div>

<?php render('includes/menu'); ?>

<div class="site">
