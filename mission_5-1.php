<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>

<h1>掲示板</h1>
<h2>好きなおにぎりの具！</h2>
  
<?php

### DB接続設定
    $dsn = 'データベース名';
    $user = 'ユーザー名';            # ユーザー名
    $password = 'パスワード';       # パスワード
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    # データベースのカラムは、id name comment date passward
    

###　変数の初期化                                            
    $edit_name = NULL;                                          # 変数の初期化                               
    $edit_comment = NULL;
    $edit_passward = NULL;                                      # 変数を宣言していないと、Notice: Undefined variable　とエラーが出る
    
    if ( ! empty($_POST["edit_num"]) ){                         # フォームの編集対象番号にデータがあれば
        $edit_num = $_POST["edit_num"];                         # $edit_numに代入（編集モード）
    }elseif ( ! empty($_POST["wether_edit"]) ){                 # 隠しフォームに編集対象番号が記録されていれば
        $edit_num = (int)$_POST["wether_edit"];                 # 隠しフォームの値を$edit_numに代入（編集モード）
    }else{                                                      # （このコードの後に変数を代入していても、プログラムを最初から実行し直すと消えるため、
                                                                #   フォームに値を入れておいて保存しておく必要がある。）
        $edit_num = NULL;                                       # いずれでもなければ、$edit_numを空にする（非編集モード）
    }
    
    $message = NULL;                                            # メッセージの内容



    ### 新規投稿
    
    if (! empty($_POST["name"])                                 # フォームの名前にデータがあり
        && ! empty($_POST["comment"])                           # フォームのコメントにデータがあり
        && empty($edit_num) ){                                  # $edit_numが空であれば
        
        if (! empty($_POST["passward"]) ){                      # さらに、フォームにパスワードがあれば                        
                                              
            $new_name = $_POST["name"];                             # $new_nameに、name="name"の入力フォームの中のデータ（文字）を代入する
            $new_comment = $_POST["comment"];                       # $new_strに、name="str"の入力フォームの中のデータ（文字）を代入する
            $new_date = date("Y-m-d H:i:s");                        # $new_dataに、投稿日時を代入
            $new_passward = $_POST["passward"];                     # $new_passwardに、name="passward"の入力フォームの中のデータ（文字）を代入する
            
            ### データベースにレコードを追加
            $stmt = $pdo -> prepare("INSERT INTO mission_5_1_table (name, comment, date, passward) VALUES (:name, :comment, :date, :passward)");
            
            $stmt -> bindParam(':name', $new_name, PDO::PARAM_STR);
            $stmt -> bindParam(':comment', $new_comment, PDO::PARAM_STR);
            $stmt -> bindParam(':date', $new_date, PDO::PARAM_STR);
            $stmt -> bindParam(':passward', $new_passward, PDO::PARAM_STR);
            
            $stmt -> execute();
            
        }else{                                                  # フォームにパスワードがなければ
            $message = "パスワードを入力してください";
        }
    
    
    
    ### 編集対象番号に該当する投稿の名前・コメント・パスワードを取得
    
    }elseif (! empty($edit_num)                                 # $edit_num にデータがあり（編集モードであり）
        && empty($_POST["name"])                                # 名前と
        && empty($_POST["comment"]) ){                          # コメントが入力されていなければ
                
        if (! empty($_POST["edit_passward"]) ){                 # さらにパスワードが入力されていれば
                                             
       
            $entered_passward = $_POST["edit_passward"];            # 入力されたパスワードを$entered_passwardに代入
            
            
            ### データベースから、id = $edit_num のname comment passward を取得
            
            $sql = 'SELECT name,comment,passward FROM mission_5_1_table WHERE id=:id ';
            $stmt = $pdo->prepare($sql);  
            
            $stmt->bindParam(':id', $edit_num, PDO::PARAM_INT); 
            $stmt->execute();                            
            $results = $stmt->fetchAll(); 
            
        
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                $recorded_name = $row['name'];                      # $recorded_nameはid = $edit_num のname
                $recorded_comment = $row['comment'];                # $recorded_commentはid = $edit_num のcomment
                $recorded_passward = $row['passward'];              # $recorded_passwardはid = $edit_num のpassward
            }
            
            
            if ($entered_passward == $recorded_passward){           # 入力されたパスワードと記録されているパスワードが一致すれば
                $edit_name = $recorded_name;                        # $edit_nameに代入（フォームに文字が表示される）
                $edit_comment = $recorded_comment;
                $edit_passward = $recorded_passward;
            }else{                                                  # パスワードが一致しなければ
                $message = "パスワードが違います";                  # メッセージを設定
                $edit_num = NULL;                                   # $edit_num を初期化（編集モード解除）
            }
            
        }else{                                                  # パスワードが入力されていなければ
            $message = "パスワードを入力してください";          # メッセージを設定
        }
        
            
        
    
    
    
    ### 編集を反映させる
    
    }elseif (! empty($_POST["name"])                            # フォームに名前と
        && ! empty($_POST["comment"])                           # コメントと
        && ! empty($edit_num)                                   # パスワードが入力されていて
        && ! empty($_POST["passward"]) ){                       # $edit_num にデータがあれば（編集モードであれば）

        $edited_name = $_POST["name"];                          # $edeited_nameに、name="name"の入力フォームの中のデータ（文字）を代入する
        $edited_comment = $_POST["comment"];                               
        $edited_date = date("Y-m-d H:i:s");
        $edited_passward = $_POST["passward"];
        
        
        
        
        ### データベースに編集済みの投稿をUPDATEする
        
        $sql = 'UPDATE mission_5_1_table SET name=:name,comment=:comment,date=:date,passward=:passward WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id', $edit_num, PDO::PARAM_INT);
        $stmt->bindParam(':name', $edited_name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $edited_comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $edited_date, PDO::PARAM_STR);
        $stmt->bindParam(':passward', $edited_passward, PDO::PARAM_STR);
        
        $stmt->execute();

        
        
        $edit_num = NULL;                                       # 編集が終了したので、$edit_numを初期化
                                                                # この場合、編集対象番号を入力すると、編集して送信するまでは編集モードになる       
    }    
    
        
        
    

    ### ファイルのうち、削除対象番号の行を消す
        
    if (! empty($_POST["delete_num"])                           # フォームに削除番号と
        && ! empty($_POST["delete_passward"]) ){                # パスワードが入力されていれば
            
        $delete_num = $_POST["delete_num"];                     # 削除対象番号を代入
        $entered_passward = $_POST["delete_passward"];          # 入力されたパスワードを代入
        
        ### データベースから、記録されているパスワードを取得
        $sql = 'SELECT passward FROM mission_5_1_table WHERE id=:id ';
        $stmt = $pdo->prepare($sql);  
        $stmt->bindParam(':id', $delete_num, PDO::PARAM_INT); 
        $stmt->execute(); 
        
        $results = $stmt->fetchAll(); 
        foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
            $recorded_passward = $row["passward"];              # 記録されているパスワードを代入
        }

        
        if ($entered_passward == $recorded_passward){           # 入力されたパスワードと、記録されているパスワードが一致すれば
            
            ### データベースからid = $delete_numのレコードを削除
            $sql = 'DELETE FROM mission_5_1_table WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $delete_num, PDO::PARAM_INT);
            $stmt->execute();
        }else{                                                  # パスワードが一致しなければ
            $message = "パスワードが違います";
        }
    }
            
       
    
    
    echo $message;
    echo "<br>";
     
    
?>

    <form action="" method="post">
        
        <input type="text" name="name" placeholder="名前" value="<?php echo $edit_name; ?>">                <!--名前入力欄、name="name"-->
        <input type="text" name="comment" placeholder="コメント" value="<?php echo $edit_comment; ?>">      <!--コメント入力欄、name="str"-->
        <input type="text" name="passward" placeholder="パスワード" value="<?php echo $edit_passward; ?>">  <!--パスワード入力欄、name="pass"-->
        <input type="submit" name="submit">                                                                 <!--送信ボタン-->
        <br>
        <input type="number" name="delete_num" placeholder="削除対象番号">                                  <!--削除対象番号、name="delete_num"-->
        <input type="text" name="delete_passward" placeholder="パスワード">                                 <!--パスワード入力欄、name="delete_pass"-->
        <input type="submit" name="delete" value="投稿を削除">                                              <!--編集ボタン-->
        <br>
        <input type="number" name="edit_num" placeholder="編集対象番号">                                    <!--編集対象番号、name="edit_num"-->
        <input type="text" name="edit_passward" placeholder="パスワード">                                   <!--パスワード入力欄、name="edit_pass"-->
        <input type="submit" name="edit" value="投稿を編集">                                                <!--編集ボタン-->
        <input type="hidden" name="wether_edit"  value="<?php echo $edit_num; ?>">                          <!--隠しフォーム-->
    </form>
    <br>
    <hr>
 
    
    <?php
        
    ### データベースのデータを表示する
    
    $sql = 'SELECT * FROM mission_5_1_table';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id']."<br>";
        echo $row['name']."<br>";
        echo $row['comment']."<br>";
        echo $row["date"]."<br>";
        echo "<hr>";    
    }
    

    
       


    ?>

</body>

</html>
