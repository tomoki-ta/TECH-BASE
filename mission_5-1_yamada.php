<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>

    <?php
	//エラーメッセージが出ないようにする(全ての機能が完成した後のみ使う)
	ini_set('display_errors',0);
	
	// データベースに接続
	$dsn = 'データベース名';//ユーザー名、データベース名、パスワードは各自設定
	$user = 'ユーザー名';
	$db_password = 'パスワード';
	$pdo = new PDO($dsn, $user, $db_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

	/*
	//テーブル削除用。今までの投稿内容全消ししたいときに。
	$sql = 'DROP TABLE tbtest_yamada';
	$stmt = $pdo->query($sql);
	*/

	//テーブル作り（もしまだこのテーブルが存在しないなら）
	$sql = "CREATE TABLE IF NOT EXISTS tbtest_yamada"
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"
	. "name char(32),"
	. "comment TEXT,"
	. "date_and_time TEXT,"
	. "password TEXT"
	.");";
	$stmt = $pdo->query($sql);

	//投稿フォームから何か送られてきた場合
	if(strlen($_POST['name']) != 0 && strlen($_POST['comment']) != 0){
		$name = $_POST['name'];
		$comment = $_POST['comment']; 
		$password = $_POST['password_frompostform']; 
		$date_and_time = date("Y/m/d H:i:s");
		//echo $name." ".$comment." ".$password." ".$date_and_time."<br>"; //←デバッグ用
			
		//新規投稿なら投稿内容をデータベースのテーブルに追加
		if (empty($_POST["edit_post"])){
			$sql = $pdo -> prepare("INSERT INTO tbtest_yamada (name, comment, password, date_and_time) VALUES (:name, :comment, :password, :date_and_time)");
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$sql -> bindParam(':password', $password, PDO::PARAM_STR);
			$sql -> bindParam(':date_and_time', $date_and_time, PDO::PARAM_STR);
			$sql -> execute();
		}
		//編集ならデータベースのテーブルの内容を変更
		else{
			$sql = 'SELECT * FROM tbtest_yamada';
			$stmt = $pdo->query($sql);
			$results = $stmt->fetchAll();
			foreach ($results as $row){
				if($row['id'] == $_POST['edit_post'] //投稿番号があってて
				   && $row['password'] == $_POST['password_frompostform']//かつパスワードもあってて
				   && !empty($row['password'])){//かつ投稿時にパスワードが設定されてたなら変更する。
					$id = $_POST["edit_post"]; //変更する投稿番号
					$name = $_POST['name'];
					$comment = $_POST['comment']; 
					$sql = 'UPDATE tbtest_yamada SET name=:name,comment=:comment,date_and_time=:date_and_time WHERE id=:id';
					$stmt = $pdo->prepare($sql);
					$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
					$stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
					$stmt -> bindParam(':date_and_time', $date_and_time, PDO::PARAM_STR);//一応時間も上書き
					$stmt -> bindParam(':id', $id, PDO::PARAM_INT);
					$stmt -> execute();
				}
			}
		}
	}

	//編集したい番号が送られてきた場合
	elseif($_POST["edit_number"]){
		$edit_post_number = $_POST["edit_number"];
		//以前の内容を投稿フォームに表示するための変数用意
		$sql = 'SELECT * FROM tbtest_yamada';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row){
			if($row['id'] == $_POST["edit_number"]){
				$edit_name = $row['name'];
				$edit_str = $row['comment'];
			}
		}	

	}

	//削除フォームから送られてきた場合→パスワードあってれば削除
	elseif($_POST['delete_number']){
		$sql = 'SELECT * FROM tbtest_yamada';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row){
			if($row['id'] == $_POST['delete_number'] 
			&& $row['password'] == $_POST['password_fromdeleteform']
			&& !empty($row['password'])){
				$id = $_POST["delete_number"];
				$sql = 'delete from tbtest_yamada where id=:id';
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
			}
		}
	}
	?>
	<?php
	//テーブルの内容をechoで表示
	$sql = 'SELECT * FROM tbtest_yamada';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
	//$rowの中にはテーブルのカラム名が入る
	echo $row['id'].' ';
	echo $row['name'].' ';
	echo $row['comment'].' ';
	//echo $row['password'].' ';//←動作確認時用
	echo $row['date_and_time'].'<br>';
	echo "<hr>";
	}
	
    ?>

<hr>
    新規投稿時にパスワードを入力したうえで投稿すると、投稿の編集・削除が可能です。<br>
    （パスワードを設定しなくても投稿は可能）<br>
    編集・削除の際にはその投稿をしたときに設定したパスワードを入力してください。
    
<hr>


<form action="" method="post">
    投稿＆編集フォーム<br>
	<input type="hidden" name="edit_post" value="<?php if(!empty($edit_post_number)){echo $edit_post_number;} ?>">
    <input type="text" name="name" placeholder="名前" value="<?php if(!empty($edit_name)){echo $edit_name;} ?>">
    <br>
    <textarea name="comment" rows="4" cols="40" placeholder="投稿内容"><?php if(!empty($edit_str)){echo $edit_str;} ?></textarea>
    <br>
    <input type="text" name="password_frompostform" placeholder="パスワード">
    <input type="submit" name="submit" value="送信">
</form>

<hr>

<form action="" method="post">
    編集したい投稿の番号
    <input type="number" name="edit_number" value="">
    <input type="submit" name="submit" value="送信">
</form>

<hr>

<form action="" method="post">
    削除する投稿の番号
    <input type="number" name="delete_number">
    <input type="text" name="password_fromdeleteform" placeholder="パスワード">
    <input type="submit" name="submit">
</form>

</body>
</html>