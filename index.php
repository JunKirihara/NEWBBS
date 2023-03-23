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
//投稿
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	//PDFアップロード.pdf以外アップロード不可
	$pdf = $_FILES['pdf'];
	if ($pdf['name'] !== '' && $pdf['error'] === 0) {
		$type = mime_content_type($pdf['tmp_name']);
		if ($type !== 'application/pdf') {
			exit('「.pdf」しかアップロードできません。');
		}
	}
	if (empty($error)) {
		if ($pdf['name'] !== '') {
			$filename=$pdf['name'];
			if (!move_uploaded_file($pdf['tmp_name'], './member_pdf/' . $filename)) {
				die('ファイルのアップロードに失敗しました');
			}
			$_SESSION['form']['pdf'] = $filename;
		} else {
			$_SESSION['form']['pdf'] = '';
		}
	}
	$pdffilename=$_FILES['pdf']['name'];
	$message = filter_input(INPUT_POST, 'message', FILTER_DEFAULT);
	$stmt = $db->prepare('insert into posts (message, member_id, pdf) values(?,?,?)');
	if (!$stmt) {
		die($db->error);
	}
	$stmt->bind_param('sis', $message, $id, $pdffilename);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="shortcut icon" href="img/man.jpeg">
<title>コミュニケーション</title>
<link rel="stylesheet" href="./css/style.css" />
</head>
<body>
<header id="header">
    <h1>CommunicationApplication</h1>
</header>
	<input type="checkbox" id="pop-up">
	<div class="overlay">
		<div class="window">
			<label class="close" for="pop-up">&#x2612;</label>
			<p class="text">メッセージを記入後、&#12300;投稿&#12301;をクリックしてください。
			<br>メッセージの先頭50文字が表示されます。<br>
			&#91;全文表示&#93;をクリックすると、メッセージ全文が表示されます。&#91;削除&#93;をクリックしてメッセージを削除できます。<br>
			&#9312;&#042;添付ファイルは、拡張子.pdf以外アップロードできません。<br>
			&#9313;&#042;ログインしたアカウント以外のメッセージを削除することはできません。</p>
		</div>
	</div>
	<div><p id="datetime"></p></div>
	<ul>
		<li><label class="open" for="pop-up">投稿・削除方法</label></li>
		<li><a href="./member.php"id="member">登録ユーザー一覧</a></li>
		<li><a href="logout.php"id="loginout">ログアウト</a></li>
	</ul>
	<form action="" method="post"enctype="multipart/form-data"id="form">
		<div>
			<div id="item"><p><?php echo 'ようこそ'.h($name); ?>さん、メッセージをどうぞ</p></div>
			<div><textarea name="message" cols="80" rows="5"id="text"></textarea></div>
			<div><input type="file" name="pdf" size="35" value=""/></div>
		</div>
		<div><button type="submit"id="btn">投稿する</button></div>
	</form>
	<hr>
	<?php
	$counts=$db->query('select count(*) as cnt from posts');
	$count=$counts->fetch_assoc();
	$max_page=floor(($count['cnt']+1)/5+1);
	$stmt = $db->prepare('select p.id, p.member_id, p.message,p.pdf,p.modified, m.name, m.picture from posts p, 
						members m where m.id=p.member_id order by id desc limit ?,5');
	if (!$stmt){
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
	$stmt->bind_result($id, $member_id, $message,$filename,$modified, $name, $picture);
	while ($stmt->fetch()):
	?>
	<div class="msg">
		<?php if ($picture): ?>
			<img src="./member_picture/<?php echo h($picture); ?>" width="48" height="48" alt="" />
		<?php endif; ?>
		<p><?php echo h(mb_substr($message,0,50));?><a href="view.php?id=<?php echo h($id); ?>">&#91;全文表示&#93;</a></p>
		<p id="contributor">
			投稿者  :<?php echo h($name);?>
			投稿時間:<?php echo h($modified);?>
			<?php if ($_SESSION['id'] === $member_id): ?>
				<a href="delete.php?id=<?php echo h($id); ?>" style="color: #F33;">&#91;削除&#93;</a>
			<?php endif; ?>
		</p>
		<?php if ($filename): ?>
			<p><a href="./member_pdf/<?php echo h($filename); ?>"download><?php echo h($filename); ?></a></p>
		<?php endif; ?>
	</div>
	<?php endwhile; ?>
	<?php if($page>$max_page):?>
		<p class="notdisplay">&#12300;表示できるものはありません。&#12301;</p>
	<?php endif; ?>
	<hr>
	<p class="page">
		<?php if($page>1):?>
			<a href="index.php?page=<?php echo $page-1;?>"><?php echo $page-1;?>ページ目へ</a> /
		<?php endif;?>
		<?php if($page<$max_page):?>
			<a href="index.php?page=<?php echo $page+1;?>"><?php echo $page+1;?>ページ目へ</a>
		<?php endif;?>
	</p>
<footer id="footer">
	<p>&copy;JunKirihara<?php echo date('Y');?></p>
</footer>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script src="./main.js"></script>
</body>
</html>