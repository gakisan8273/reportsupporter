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

// debug('redme.phpにアクセスしました--------------------------------------------------------------');
// debug('セッション',$_SESSION);
debug('readme.php アクセス',$_SESSION['user']);
?>

<?php require('head.php');?>

<body>

<!-- ヘッダー -->
<?php require('header.php');?>

<div class="container">

<h3>作った人</h3>
<p><a href="https://twitter.com/gakisan8273">がき@gakisan8273</a></p>

<h2>このwebアプリの目的</h2>
<p>以下のような学習進捗ツイートをしやすくすし、学習者の負担を少しでも下げることを目的としています</p>
<p>すぐに使いたい方は、<a href="resister.php">こちら</a>で報告フォーマットの登録をお願いします</p>


<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">Day: 83<br>today:6h<br>total: 234h<br>自作ツール(進捗報告支援)開発<br><br>ロジックが整理できた（と思う）<br><br>次やること<br>・残り機能を実装する<br>・こんな風に言語を使い分ける<br>　ーPHPはサーバからデータを引っ張ってくる、渡す<br>　ーJSはそのデータを加工する<br><br>頭が冴えてる日中にやりたい😂<a href="https://twitter.com/hashtag/%E3%82%A6%E3%82%A7%E3%83%96%E3%82%AB%E3%83%84?src=hash&amp;ref_src=twsrc%5Etfw">#ウェブカツ</a></p>&mdash; がき@地方で強く生きていく (@gakisan8273) <a href="https://twitter.com/gakisan8273/status/1166398906520883200?ref_src=twsrc%5Etfw">August 27, 2019</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

<p>このツイートをするには、通常このようなステップを踏みます</p>
<ol>
  <li>自分のプロフィールのタイムラインから、直近の報告ツイートを探す</li>
  <li>1 を開き、内容をコピーする</li>
  <li>ツイート画面で貼り付け、日数と学習時間を手で更新する</li>
  <li>余分な文を消去する</li>
  <li>本文を入力する</li>
  <li>ツイートする</li>
</ol>

<p>このwebアプリにより、このように改善されます</p>
<ol>
  <li>このwebアプリを開く</li>
  <li>専用フォームに学習時間を入力する（日数は自動更新）</li>
  <li>本文を入力する</li>
  <li>ツイートする</li>
</ol>
<p>詳細は<a href="https://note.mu/mintdaa/n/nb01c23931cde">こちらのnote</a>に記載されています</p>

<h2>使い方</h2>
<p>PCからのアクセス推奨です。スマホにも一応対応しています</p>
<p>会員登録・ログインは不要です。ブラウザに情報が保存されますのでクッキーを有効にしてください</p>
<p>初回のみ<a href="resister.php">報告フォーマットを登録</a>してください</p>
<p>これだけで新規報告ツイートの作成準備は整います</p>
<p>一度フォーマットを登録すれば、次回以降は登録し直す必要はありません</p>
<p>ブックマークは、<a href="index.php">新規報告ツイート画面</a>をお勧めします</p>

<h3>お問い合わせ</h3>
<p>できるだけ説明なしに使えるように配慮したつもりです</p>
<p>不明点・疑問点・苦情・応援・意見などあれば<a href="https://twitter.com/gakisan8273">僕のTwitterアカウント</a>まで連絡ください</p>


</div>

<!-- フッター -->
<?php require('footer.php'); ?>

</body>
</html>