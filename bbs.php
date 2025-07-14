<?php
    //変数の宣言と初期化
    date_default_timezone_set("Asia/Tokyo");
    $postDate = null;
    $comment_array = array();
    $success_messages = null;
    $error_messages = array();
    $escaped = array();
    $pdo = null;
    $stmt = null;
    $res = null;

    //DB接続
    try
    {
        $pdo = new PDO('mysql:host=localhost;dbname=bbs', "root"); 
    }
    catch(PDOException $e)
    {
        //接続エラーの際はエラー内容を取得する
        $error_messages[] = $e->getMessage();
    }

    //書き込むボタンが押された際の処理
    if(!empty($_POST["submitButton"]))
    {
        //ユーザー名チェック
        if(empty($_POST["username"]))
        {
            $error_messages[] =  "ユーザー名を入力してください";
        }
        else
        {
            $escaped['username'] = htmlspecialchars($_POST["username"], ENT_QUOTES, "UTF-8");
        }
    
        //コメントチェック
        if(empty($_POST["comment"]))
        {
            $error_messages[] =  "コメントを入力してください";
        }
        else
        {
            $escaped['comment'] = htmlspecialchars($_POST["comment"], ENT_QUOTES, "UTF-8");
        }

        //エラーメッセージがなければデータ保存
        if(empty($error_messages))
        {
            $postDate = date("Y-m-d H:i:s");
            $pdo -> beginTransaction();
            try
            {
                $stmt = $pdo ->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate)");
                $stmt -> bindParam(':username', $escaped["username"], PDO::PARAM_STR);
                $stmt -> bindParam(':comment', $escaped["comment"], PDO::PARAM_STR);
                $stmt -> bindParam(':postDate', $postDate, PDO::PARAM_STR);
                $res = $stmt -> execute();
                $res = $pdo -> commit();
            }
            //エラーが発生すればロールバック
            catch(Exception $e)
            {
                $pdo -> rollBack();
            }

            if($res)
            {
                $success_messages = "コメントを書き込みました";
            }
            else
            {
                $error_messages[] = "書き込みに失敗しました"; 
            }

            $statement = null;
        }
    }

    //DBからデータを取得
    $sql ="SELECT `id`, `username`, `comment`, `postDate` FROM `bbs-table`";
    $comment_array = $pdo->query($sql);

    //DB接続を閉じる
    $pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>第二新卒・既卒のための掲示板</title>
    <link rel="stylesheet" href="style3.css">
</head>

<body>
    <h1 class = "title"> 第二新卒・既卒のための掲示板 </h1>
    <hr>
    <div class = "boardWrapper">
        <!-- コメント成功時 -->
        <?php if(!empty($success_messages)) : ?>
            <p class = "success_messages"><?php echo $success_messages; ?> </p>
        
        <?php endif; ?>

        <!-- バリデーションチェック時 -->
        <?php if (!empty($error_messages)) : ?>
            <?php foreach ($error_messages as $value) : ?>
                <div class = "error_messages">※<?php echo $value; ?> </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <section>
            <?php if (!empty($comment_array)) : ?>
                <?php foreach($comment_array as $comment): ?>
                    <article>
                        <div class = "wrapper">
                            <div class = "nameArea">
                                <span> 名前： </span>
                                <p class = "username"><?php echo $comment['username']; ?></p>
                                <time>:<?php echo $comment['postDate']; ?></time>
                            </div>
                            <p class = "comment"><?php echo $comment['comment']; ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <form class = "formWrapper" method = "POST" action = "">
            <div>
                <label for = ""> ユーザー名： </label>
                <input type ="text" name = "username">
                <input type = "submit" value = "書き込む" name = "submitButton">
            </div>
            <div>
                <textarea class="commentTextArea" name = "comment"></textarea>
            </div>
        </form>
    </div>
</body>
</html>