<?php
session_start();
require('../library.php');//DB接続

if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
	$form = $_SESSION['form'];
} else {
	$form = [
		'name' => '',
		'email' => '',
		'password' => '',
	];
}
$error = [];
/*フォーム内容チェック*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$form['name'] = filter_input(INPUT_POST, 'name', FILTER_DEFAULT);
	if ($form['name'] === '') {
		$error['name'] = 'blank';
	}

	$form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	if ($form['email'] === '') {
		$error['email'] = 'blank';
	} else {
		$db = dbconnect();
		$stmt = $db->prepare('select count(*) from members where email=?');
		if (!$stmt) {
			die($db->error);
		}
		$stmt->bind_param('s', $form['email']);
		$success = $stmt->execute();
		if (!$success) {
			die($db->error);
		}

		$stmt->bind_result($cnt);
		$stmt->fetch();

		if ($cnt > 0) {
			$error['email'] = 'duplicate';
		}
	}

	$form['password'] = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
	if ($form['password'] === '') {
		$error['password'] = 'blank';
	} else if (strlen($form['password']) < 4) {
		$error['password'] = 'length';
	}
	//アイコン拡張子「.png」「.jpg」のみアップロード可
	$image = $_FILES['image'];
	if ($image['name'] !== '' && $image['error'] === 0) {
		$type = mime_content_type($image['tmp_name']);
		if ($type !== 'image/png' && $type !== 'image/jpeg') {
			$error['image'] = 'type';
		}
	}

	if (empty($error)) {
		$_SESSION['form'] = $form;
		//画像アップロード
		if ($image['name'] !== '') {
			$filename = date('YmdHis') . '_' . $image['name'];
			if (!move_uploaded_file($image['tmp_name'], '../member_picture/' . $filename)) {
				die('アップロードに失敗しました');
			}
			$_SESSION['form']['image'] = $filename;
		} else {
			$_SESSION['form']['image'] = '';
		}

		header('Location: check.php');
		exit();
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="shortcut icon" href="../img/man.jpeg">
<title>ユーザー登録</title>
<link rel="stylesheet" href="../css/jindexstyle.css" />
</head>
<body>
<div id="wrapper">
<header id="header">
    <h1>CommunicationApplication</h1>
  </header>
	<h2 id="guide">次のフォームに必要事項をご記入ください。</h2>
	<div id="form">
	<form action="" method="post" enctype="multipart/form-data">
		<div class="container">
			<div>
			<div class="item"><p>氏名<span class="required">&lt;必須&gt;</span></p></div>
			<div>
			<input type="text" name="name" size="35" maxlength="255" value="<?php echo h($form['name']); ?>"  class="txt2"/>
			<?php if (isset($error['name']) && $error['name'] === 'blank') : ?>
			<p class="caution">&#042;氏名を入力してください。</p>
			<?php endif; ?>
			</div>
			<div class="item"><p>メールアドレス<span class="required">&lt;必須&gt;</span></p></div>
			<input type="text" name="email" size="35" maxlength="255" value="<?php echo h($form['email']); ?>"  class="txt2"/>
			<?php if (isset($error['email']) && $error['email'] === 'blank') : ?>
			<p class="caution">&#042;メールアドレスを入力してください。</p>
			<?php endif; ?>
			<?php if (isset($error['email']) && $error['email'] === 'duplicate') : ?>
			<p class="caution">&#042;指定されたメールアドレスはすでに登録されています。</p>
			<?php endif; ?>
			<div class="item"><p>パスワード<span class="required">&lt;必須&gt;</span></p></div>
			<div>
			<input type="password" name="password" size="10" maxlength="20" value="<?php echo h($form['password']); ?>"class="txt2"/>
			<?php if (isset($error['password']) && $error['password'] === 'blank') : ?>
			<p class="caution">&#042;パスワードを入力してください。</p>
			<?php endif; ?>
			<?php if (isset($error['password']) && $error['password'] === 'length') : ?>
			<p class="caution">&#042;パスワードは4文字以上で入力してください。</p>
			<?php endif; ?>
			</div>
			<div class="item"><p>アイコン登録</p></div>
			<div>
			<input type="file" name="image" size="35" value="" />
			<?php if (isset($error['image']) && $error['image'] === 'type') : ?>
			<p class="caution">&#042;アイコンは「.png」または「.jpg」の画像を指定してください。</p>
			<?php endif; ?>
			</div>
			</div>
			<div class="parent"><button type="submit"class="btn --flex">入力内容を確認する</button></div>
		</div>
	</form>
	</div>	
	<footer id="footer">
		<p>&copy;JunKirihara<?php echo date('Y');?></p>
	</footer>
</div>
</body>
</html>