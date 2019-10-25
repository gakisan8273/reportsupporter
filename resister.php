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
// debug('resister.phpにアクセスしました------------------------------------------------------------------------------------');
// debug('セッション',$_SESSION);
debug('resister.php アクセス',$_SESSION['user']);
//セッションに情報を詰める
if (empty($_POST)){
  //POSTされなければなにもしない
  // debug('POSTされていません','');
 }else{
   //POSTされていたら、セッションに情報を詰める
   $user = $_POST['user'];
   $_SESSION['user'] = $user;
   $tweetFormatAll = $_POST['tweet_format'];
   $_SESSION['tweet_format'] = $tweetFormatAll;
   $_SESSION['hashTags'] = $_POST['hashTags'];
  //  debug('POST', $_POST);
  debug('resister.php POST',$_SESSION['user']);
  // ['calc_option']があれば、継承　なければ1にする
  if(!($_SESSION['calc_option'])){
    $_SESSION['calc_option'] = 1;
  }

   //フォーマットの{ の数を数える
   $_SESSION['number_count']= mb_substr_count($_SESSION['tweet_format'] , '{');

   //フォーマットの中の数字を置換して一般化する
   $replacePattern = '/\{[^}]*\}+/u';
   $replacement = '{*+*}';
   $replacedTweetFormat = preg_replace($replacePattern, $replacement, $tweetFormatAll);
   $replacePattern = '/\[[^]]*\]+/u';
   $replacement = '[*+*]';
   $replacedTweetFormat = preg_replace($replacePattern, $replacement, $replacedTweetFormat);
   $_SESSION['replaced_tweet_format'] = $replacedTweetFormat;
  //  debug('置換後のフォーマット',$replacedTweetFormat);
 
 
   // フォーマットをexplodeする
   $tweetFormat = explode("\n", $tweetFormatAll);
   //フォーマットの行数
   $rowsFormat = count($tweetFormat);
   $searchWordsArray = array();
   $searchWords = array();
   $searchReplaceRows = array();
   $replaceRows = array();
   $searchCopyRows = array();
 
   for ($i=0; $i < $rowsFormat; $i++) { 
     // １行目~最後の行まで１行ごと文字列を検索
     //報告ツイート検索用ワード
   // 数値でない文字列（日本語・半角英語・全角英語）を抽出し、それらを含むツイートを検索する
   // 前回のコピーの行、空欄は含めない
     $pattern = '/[a-zA-Zぁ-んァ-ヶー一-龠]+/u'; // 連続した英字、ひらがな、カタカナ、漢字を検索　半角・全角スペースは含まれない
     preg_match_all($pattern, $tweetFormat[$i], $searchWordsArray[$i] );//tweetFormat[*]が行数　これをキーとしてForループすればいい
     // debug('検索ワード１行目',$searchWordsArray[0][0]);
     foreach($searchWordsArray[$i][0] as $val){
       $searchWords[] = $val; //過去ついから報告を検索するためのワード
     }
 
     // {}が含まれる行にフラグを立てる　過去ついの数字を置換する対象の行に使う
     $pattern = '/\{.*\}+/u';
     preg_match_all($pattern, $tweetFormat[$i], $searchReplaceRows[$i] ); //$searchReplaceRows[$i][0]が空でなければ{}が含まれている
     // debug('$searchReplaceRows[$i]',$searchReplaceRows[$i][0]);
     if ( !empty($searchReplaceRows[$i][0]) ) {
       $_SESSION['replace_rows'][$i] = 1;
     }else{
       $_SESSION['replace_rows'][$i] = 0;
     }
 
     // []が含まれる行にフラグを立てる（日数のため）　過去ついの数字を置換する対象の行に使う
       $pattern = '/\[.*\]+/u';
       preg_match_all($pattern, $tweetFormat[$i], $searchReplaceRows[$i] ); //$searchReplaceRows[$i][0]が空でなければ[]が含まれている
       // debug('$searchReplaceRows[$i]',$searchReplaceRows[$i][0]);
       if ( !empty($searchReplaceRows[$i][0]) ) {
         $_SESSION['replace_rows_day'][$i] = 1;
       }else{
         $_SESSION['replace_rows_day'][$i] = 0;
       }
 
     //全ついからコピーする行を指定する　"*---*"を検知するとその行はコピーとする
     $pattern = '/\*---\*/u';
     preg_match_all($pattern, $tweetFormat[$i], $searchCopyRows[$i] ); //$searchCopyRows[$i][0]が空でなければコピーする行
     if ( !empty($searchCopyRows[$i][0]) ) {
       $_SESSION['copy_rows'][$i] = 1;
     }else{
       $_SESSION['copy_rows'][$i] = 0;
     }
   }
   // debug('コピーする行',$_SESSION['copy_rows'] );
   // debug('{}が含まれている行',$_SESSION['replace_rows']); //$searchReplaceRows[$i][0]が空でなければ{}が含まれている
   // 正規表現検索用に展開
   $patternForSearchTweets = array();
   foreach($searchWords as $val){
     $patternForSearchTweets[] = $val;
   }
  //  debug('正規表現用検索パターン',$patternForSearchTweets);
   $_SESSION['pattern'] = $patternForSearchTweets; //頭から数個に減らしてもいいと思う
   $_SESSION['rows_format'] = $rowsFormat;
 
   header("Location:index.php");
   exit();

  
 }//セッションに情報詰め終わり
 $example1 = '';
 $example1 = <<<EOT
Day : [85]　　　　　　　　　　
Today : {5.5}h / 補足 {2}h　　　
Total : {50時間30分} / 補足 {75h}  
*---*

// 【フォーマット登録規則】
// 日数は[ ]で囲う
// 毎回更新したい数字を{ } で囲う
// 単位も囲うことができる
// 前回ツイートからコピペしたい行は *---* を入力
// ハッシュタグはここに記入するか、上の入力欄に記入する
EOT;
?>


<?php require('head.php');?>

<body>
  
<?php require('header.php');?>

<div class="container">

  <form action="" method="post" class="justify-content-center">

  <div class="d-flex"> <!-- ユーザ名入力------------------------------------------ -->

    <div class="form-group col-sm-6">
      <label>UserName : </label>
      <div class="input-group">
        <div class="input-group-prepend"><div class="input-group-text">@</div></div>
        <input class="form-control" type="text" name="user" placeholder="twitterID @無しで入力" value="<?php echo ($_SESSION['user'])? htmlspecialchars($_SESSION['user']) : '' ?>">
      </div>
    </div>

    <div class="form-group col-sm-6 d-none d-sm-block">
      <label>(ex)UserName : </label>
      <div class="input-group">
        <div class="input-group-prepend"><div class="input-group-text">@</div></div>
        <input class="form-control" disabled type="text" name="user_example" value="gakisan8273">
      </div>
    </div>

  </div>
<!-- ユーザ名入力------------------------------------------ -->


<div class="d-flex"> <!-- ハッシュタグ入力------------------------------------------ -->

    <div class="form-group col-sm-6">
      <label>HashTags : </label>
      <div class="input-group">
        <!-- <div class="input-group-prepend"><div class="input-group-text">#</div></div> -->
        <textarea cols="15" rows="5" class="form-control" type="text" placeholder="ハッシュタグ #付きで入力" name="hashTags"><?php echo ($_SESSION['hashTags'])? htmlspecialchars($_SESSION['hashTags']) : '' ?></textarea>
      </div>
    </div>

    <div class="form-group col-sm-6 d-none d-sm-block">
      <label>(ex)HashTags : </label>
      <div class="input-group">
        <!-- <div class="input-group-prepend"><div class="input-group-text">#</div></div> -->
        <textarea disabled cols="15" rows="5" class="form-control" type="text" name="hashTags_example">#ウェブカツ
#ウェブカツ女性割引
#駆け出しエンジニアと繋がりたい</textarea>
    </div>
    </div>


</div>
<!-- ハッシュタグ入力------------------------------------------ -->

    <div class="d-flex"><!-- 報告フォーマット入力------------------------------------------ -->

      <div class="form-group col-sm-6">
        <label class="d-block">Format : </label>
          <div class="mb-2">
              <p class="d-inline-block small">選択範囲の前後に</p><br class="d-block d-sm-none">
              <button type="button" class="small d-inline-block js-insert-day btn-info">[]を挿入(Day)</button>
              <button type="button" class="small d-inline-block js-insert-time btn-info">{}を挿入(time)</button>
              <button type="button" class="small d-inline-block js-insert-copy btn-info">*---*を挿入(コピペ行)</button>
          </div>
        <textarea id="" cols="30" rows="11" name="tweet_format" class="form-control js-format" placeholder="自分の過去ツイートから進捗報告ツイートをコピペし、下の'(ex)format' の規則を参考にして編集してください"
                  value=""><?php echo ($_SESSION['tweet_format'])? htmlspecialchars($_SESSION['tweet_format']) : '' ?></textarea>
      </div>

      <div class="form-group col-sm-6 d-none d-sm-block">
        <label for="">(ex)Format : </label>
        <div class="d-flex align-content-between flex-wrap">
          <textarea readonly name="" id="" cols="30" rows="11" class="form-control"><?php echo $example1; ?></textarea>
        </div>
      </div>

    </div>
<!-- 報告フォーマット入力------------------------------------------ -->
  <div id="ex-format" class="d-flex justify-content-around">
    <input type="submit" class="btn btn-primary col-sm-4" value="フォーマット登録">
    <input type="" class="btn btn-primary col-sm-4 invisible d-none d-sm-block">
  </div>

  <div class="form-group col-sm-6 d-block d-sm-none mt-4">
      <label for="">(ex)Format : </label>
      <div class="d-flex align-content-between flex-wrap">
          <textarea readonly name="" id="" cols="30" rows="11" class="form-control"><?php echo $example1; ?></textarea>
      </div>
  </div>

	</form>

  </div>

</div>

<!-- フッター -->
<?php require('footer.php'); ?>
<script src="js/resister.js"></script>
</body>
</html>