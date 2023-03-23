<?php
session_start();
require('../library.php');//DB接続

if (isset($_SESSION['form'])) {
	$form = $_SESSION['form'];
} else {
	header('Location: index.php');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$db = dbconnect();
	$stmt = $db->prepare('insert into members (name, email, password, picture) VALUES (?, ?, ?, ?)');
	if (!$stmt) {
		die($db->error);
	}
	$password = password_hash($form['password'], PASSWORD_DEFAULT);
	$stmt->bind_param('ssss', $form['name'], $form['email'], $password, $form['image']);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}
	unset($_SESSION['form']);
	header('Location: thanks.php');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="shortcut icon" href="../img/man.jpeg">
<title>登録情報確認</title>
<link rel="stylesheet" href="../css/jcheckstyle.css" />
</head>
<body>
<div id="wrapper">
	<header id="header">
		<h1>CommunicationApplication</h1>
	</header>
	<p class="caution">&#042;内容を確認して、「登録する」ボタンをクリックしてください。</p>
	<div class="container">
		<form action="" method="post">
			<div class="item"><p>氏名&colon;<?php echo h($form['name']); ?></p></div>					
			<div class="item"><p>メールアドレス&colon;<?php echo h($form['email']); ?></p></div>
			<div class="item"><p>パスワード&colon;&#091;パスワード保護のため、表示できません。&#093;</p></div>
			<div class="item"><p>アイコン登録<?php if ($form['image']) : ?><img src="../member_picture/<?php echo h($form['image']); ?>" width="100" alt="" /><?php endif; ?></p></div>
			<div><a href="index.php?action=rewrite">&laquo;記入内容を変更する</a><button type="submit"id="btn">登録する</button></div>
		</form>
	</div>
	<footer id="footer">
	<p>&copy;JunKirihara<?php echo date('Y');?></p>
	</footer>
</div>
</body>
</html>