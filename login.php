<?php
session_start();
require('library.php');//DB接続
//ログインチェック
$error = [];
$email = '';
$password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
  if ($email === '' || $password === '') {
    $error['login'] = 'blank';
  } else {
    $db = dbconnect();
    $stmt = $db->prepare('select id, name, password from members where email=? limit 1');
    if (!$stmt) {
      die($db->error);
    }
    $stmt->bind_param('s', $email);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }
    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();
    if (password_verify($password, $hash)) {
    //ログイン成功
      session_regenerate_id();
      $_SESSION['id'] = $id;
      $_SESSION['name'] = $name;
      header('Location: index.php');
      exit();
    } else {
      $error['login'] = 'failed';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<title>login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="./css/loginstyle.css" />
<link href="https://fonts.googleapis.com/css?family=Sawarabi+Gothic" rel="stylesheet">
<link rel="shortcut icon" href="../img/man.jpeg">
</head>
<body>
<div id="wrapper">
  <header id="header">
    <h1>CommunicationApplication</h1>
  </header>
  <div id="register">
    <p id="unregis">&#042;ユーザー登録がまだの方はこちらから</p>
    <div class="parent"><a href="join/" class="btn --flex">ユーザー登録する</a></div>
  </div>
  <h2 id="guide">メールアドレスとパスワードを入力してログインしてください。</h2>
  <div id="form">
    <form action="" method="post">
      <div class="container">
        <div class="head">
          <h2>Say Hello</h2>
        </div>
        <div>
          <div class="item"><p>メールアドレス</p></div>
        <div>
          <input type="text" name="email" size="35" maxlength="255" value="<?php echo h($email); ?>" />
          <?php if (isset($error['login']) && $error['login'] === 'blank'): ?>
            <p class="caution">&#042;メールアドレスとパスワードをご記入ください。</p>
          <?php endif; ?>
          <?php if (isset($error['login']) && $error['login'] === 'failed'): ?>
            <p class="caution">&#042;ログインに失敗しました。正しくご記入ください。</p>
          <?php endif; ?>
        </div>
        <div class="item"><p>パスワード</p></div>
          <div><input type="password" name="password" size="35" maxlength="255" value="<?php echo h($password); ?>" /></div>
        </div>
        <div class="parent"><input type="submit" value="ログイン" class="btn --flex"></div>
      </div>
    </form>
  </div>
  <footer id="footer">
  <p>&copy;JunKirihara<?php echo date('Y');?></p>
  </footer>
</div>
</body>
</html>
