<?php
session_start();
require('library.php');//DB接続
$error=[];
//ログイン
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
	$id = $_SESSION['id'];
	$name = $_SESSION['name'];
} else {
	header('Location: login.php');
	exit();
}
$db = dbconnect();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="shortcut icon" href="img/man.jpeg">
<title>登録ユーザー一覧</title>
<link rel="stylesheet" href="./css/memberstyle.css" />
</head>
<body>
<div id="wrapper">
<header id="header">
    <h1>CommunicationApplication</h1>
  </header>
<div id="home"><a href="index.php">&laquo;コミュニケーションページへ戻る</a></div>
	<hr>
	<?php
		$counts=$db->query('select count(*) as cnt from members');
		$count=$counts->fetch_assoc();
		$max_page=floor(($count['cnt']+1)/5+1);
		$stmt = $db->prepare('select id,name, picture, email from members order by id desc limit ?,5');
		if (!$stmt) {
			die($db->error);
		}
		$page= filter_input(INPUT_GET,'page',FILTER_SANITIZE_NUMBER_INT);
		if(!$page){
			$page=1;
		}
		$start=($page-1)*5;
		$stmt->bind_param('i',$start);
		$suc = $stmt->execute();
		if(!$suc){
			die($db->error);
		}
		$stmt->bind_result( $id,$name, $picture, $email);
		while ($stmt->fetch()):
	?>
	<div class="msg">
		<?php if ($picture): ?>
		<img src="./member_picture/<?php echo h($picture); ?>" width="48" height="48" alt="" />
		<?php endif; ?>
		<p><span>ID:<?php echo h($id); ?></span></p>
		<p><span>氏名:<?php echo h($name); ?></span></p>
		<p>連絡先:<?php echo h($email); ?></a></p>
	</div>
	<?php endwhile; ?>
	<?php if($page>$max_page):?>
		<p class="notdisplay">&#12300;表示できるものはありません。&#12301;</p>
	<?php endif; ?>
	<hr>
	<p class="page">
	<?php if($page>1):?>
		<a href="member.php?page=<?php echo $page-1;?>"><?php echo $page-1;?>ページ目へ</a> /
	<?php endif;?>
	<?php if($page<$max_page):?>
		<a href="member.php?page=<?php echo $page+1;?>"><?php echo $page+1;?>ページ目へ</a>
	<?php endif;?>
	</p>
	<footer id="footer">
		<p>&copy;JunKirihara<?php echo date('Y');?></p>
	</footer>
</div>
</body>
</html>