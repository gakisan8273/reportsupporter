<?php
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');
ini_set('display_errors','off');
error_reporting(E_ALL & ~E_NOTICE);

function debug($title,$str = ''){
	$msg = $title.' : ';
	return error_log($msg.print_r($str,true)); //配列はPrint_rしないとArrayと表示されるだけになってしまう
}
//================================
// セッション準備・セッション有効期限を延ばす
//================================
// セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない　/tmp/だと早く削除される
session_save_path("/var/tmp/");
// ガーベージコレクションが削除するセッションの有効期限を設定（1年以上経っているものに対してだけ100分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30*12);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60*60*24*30*12);
// セッションを使う　セッションの設定はsession_startの前にする
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();
// session_unset();
// session_destroy();
//ログイン有効期間を１年とする
$sesLimit = 60*60*24*30*12;
$_SESSION['login_date'] = time();
$_SESSION['login_limit'] = time() + $sesLimit;

// debug('セッション',$_SESSION);
debug('calcday.php アクセス',$_SESSION['user']);
//セッションに情報を詰める
if (empty($_POST)){
  //POSTされなければなにもしない
  // debug('POSTされていません','');
 }else{
  //  debug('POST',$_POST);
  debug('calcday.php POST',$_SESSION['user']);
  //バリデーション

  // Option3が選択されている時、日付を入力必須にする
  // 入力されていれば、形式チェックをする
  $errmsg = '';
  if((int)$_POST['calc_option'] === 3){
    // debug('入力されているか');
    if(empty($_POST['startdate'])){
      $errmsg = 'Option3選択時 入力必須です';
    } else{
      // debug('入力OK');
      // debug('形式チェック');
      if(!preg_match('/^20[0-9]{2}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/' , $_POST['startdate'] ) ){
        $errmsg = '日付は yyyy-mm-dd 形式で入力してください';
      }else{
        // debug('形式OK');
      }
    }
  }

  if(empty($errmsg)){
    // debug('バリデーションOK');
    $_SESSION['calc_option'] = $_POST['calc_option'];
    $_SESSION['startdate'] = $_POST['startdate'];
    // debug('トップに遷移します');
    header("Location:index.php");
    exit();
  }
  // debug('バリデーションNG');
  // debug('エラー',$errmsg);
}


?>

<?php require('head.php');?>

<body>

<!-- ヘッダー -->
<?php require('header.php');?>

<div class="container bg-light p-3">
	<form action="" method="post">
  <div class="form-check mb-4">
    <input class="form-check-input" type="radio" name="calc_option" id="radio1a" value="1" <?php echo ((int)$_SESSION['calc_option']===1)? 'checked': '';?>>
    <label class="form-check-label" for="radio1a">Option 1 (default)</label><br>
    <label class="form-check-label" for="radio1a">前回の報告ツイート内にある日数を取得し、そこから何日経過したかを計算します</label>
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="radio" name="calc_option" id="radio1b" value="2" <?php echo ((int)$_SESSION['calc_option']===2)? 'checked':'';?>>
    <label class="form-check-label" for="radio1b">Option 2</label><br>
    <label class="form-check-label" for="radio1b">前回の報告ツイート内にある日数を取得し、それに+1します</label>
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="radio" name="calc_option" id="radio1c" value="3" <?php echo ((int)$_SESSION['calc_option']===3)? 'checked':'';?>>
    <label class="form-check-label" for="radio1c">Option 3</label><br>
    <label class="form-check-label" for="radio1c">学習開始日を設定し、そこから何日経過したかを計算します</label><br>

    <div class="input-group">
      <div class="input-group-prepend"><div class="input-group-text">学習開始日</div></div>
      <input type="text" class="form-control col-sm-2 js-option3-date" placeholder="yyyy-mm-dd" name="startdate"
      value="<?php echo ($_SESSION['startdate'])? htmlspecialchars($_SESSION['startdate']):'' ?>">
    </div>
  </div>

<!-- <div class="container bg-secondary">
  <div class="input-group">
    <label for="" class="">学習当日に報告ツイートをできなかった時のための機能<br></label>
    <label for="" class="">(ex)18〜24時までのツイートは当日分とみなし、それ以外の時間は前日分とみなす</label><br>
    <div class="input-group-prepend"><div class="input-group-text">この時間以降のツイートは当日の報告とみなす</div></div>
    <input type="tel" class="form-control col-sm-1" placeholder="0 to 24">
    <div class="input-group-append"><div class="input-group-text">時</div></div>
  </div>
</div> -->

  <input class="btn btn-primary col-sm-4" type="submit" value="変更する">
</form>

</div>
<!-- 過去ツイート -->

<!-- フッター -->
<!-- フッター -->
<?php require('footer.php'); ?>
<script src="js/calcday.js"></script>
</body>
</html>