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


//まずはJsonが取得できるか試す　その後整形する
// 指定ユーザーのタイムラインから、指定ワードがあるものを5件抽出する

//token.phpで取得したベアラートークン
require('token.php');

//ユーザーのタイムライン
// https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-user_timeline　から
$requestUrl ='https://api.twitter.com/1.1/statuses/user_timeline.json';

// debug('index.phpにアクセスしました------------------------------------------------------------------');
// debug('セッション',$_SESSION);

if(empty($_SESSION['user'])){
	//ユーザーがセッションになければ何もしない
	// debug('セッションにユーザ名がありません','');
	// debug('はじめに画面へ遷移します');
	header("Location:readme.php");
	exit();
}else{
	//リクエストURLにパラメータを追加
	// debug('セッションにユーザー名が保存されています');
	$count = 100;
	$params = array(
		'screen_name' => $_SESSION['user'] ,
		'count' => $count ,
		// 'trim_user' => true,
		'include_rts' => false,
		'exclude_replies' => true,
		'tweet_mode' => 'extended',//ツイート全文が表示されるようにする　jsonがtextでなくfull_textになることに注意
	) ;
	// debug('params',$params);
	debug('index.php アクセス',$_SESSION['user']);
	// クエリ形式に変換
	if($params){
			$Qparams = http_build_query($params);
			$requestUrl .= '?'.$Qparams;
	}

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => 'GET' , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: Bearer '. $bearerToken,
			) ,
		) ,
	) ;

	// //ストリームコンテキストを作成　file_get_contentsのオプションとして使う時に必要　詳しくは知らん
	$Scontext = stream_context_create($context);
	// debug('コンテキスト',$Scontext);
	$json = file_get_contents($requestUrl, false, $Scontext);//ただの文字列（JSON？）なので、あとで連想配列に変換する
	// debug('リクエスト',$requestUrl);
	// var_dump($Scontext);
	// var_dump($json);
	//これが原因で４０１エラーが出ているようす curlを使ってリクエスト→エラーなし　その後file_get?contentsで再トライ→エラーでず　なんで
	// サイトをはじめに開いた時はJson取得できていない　オーソライズエラー
	// POSTすると取得できる　なんで　リクエスト送信できていない？　ストリームコンテキストのリソースがID2(エラーの時)　ID4（成功の時）　なんで変わる・・・


	// //-------------------------------------------------------------------------------------
	// // cURLを使ってリクエスト
	// $curl = curl_init() ;
	// curl_setopt( $curl, CURLOPT_URL, $requestUrl ) ;	// リクエストURL
	// curl_setopt( $curl, CURLOPT_HEADER, true ) ;	// ヘッダーを取得する
	// curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $context['http']['method'] ) ;	// メソッド
	// curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;	// 証明書の検証を行わない
	// curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;	// curl_execの結果を文字列で返す
	// curl_setopt( $curl, CURLOPT_HTTPHEADER, $context['http']['header'] ) ;	// ヘッダー
	// curl_setopt( $curl, CURLOPT_TIMEOUT, 5 ) ;	// タイムアウトの秒数
	// $res1 = curl_exec( $curl ) ;
	// $res2 = curl_getinfo( $curl ) ;
	// curl_close( $curl ) ;

	// // 取得したデータ
	// $json = substr( $res1, $res2['header_size'] ) ;	// 取得したデータ(JSONなど)
	// $header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = @file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONを変換
	// $obj = json_decode( $json ) ;	// オブジェクトに変換
	// $arr = json_decode( $json, true ) ;	// 配列に変換
	//-------------------------------------------------------------------------------------


	$tweets = json_decode($json, true); //jsonを連想配列に変換 falseだとオブジェクトに変換
	// debug('レスポンス',$tweets);

	//ツイートクラスを作ってみる
	class Tweets{
		protected $tweetText;
		protected $created_at;
		protected $studyDaysTotal;
		protected $hashTags;

		public function __construct($tweetText, $created_at, $hashTags){
			$this->tweetText = $tweetText;
			$this->created_at = $created_at;
			$this->hashTags = $hashTags;
			// debug('ハッシュタグ',$this->hashTags);

		}

		//ゲッター
		public function getTweetText(){
			return $this->tweetText;
		}
		public function getCreatedAt(){
			return $this->created_at;
		}
		public function getStudyDaysTotal(){
			return $this->studyDaysTotal;
		}
		public function getHashTags(){
			return $this->hashTags;
		}

		//セッター

	}//Tweetクラスの終わり

	class PreviousTweets extends Tweets{
		protected $previousDays;
		protected $previousTimes;

		public function __construct($tweetText, $created_at, $hashTags){
			// debug('PreviousTweet生成');
			$this->tweetText = $tweetText;
			$this->created_at = $created_at;
			$this->hashTags = $hashTags;
			// debug('ハッシュタグ',$this->hashTags);
			$this->previousDays = $this->getDaysFromPreviousTweet();
			$this->previousTimes = $this->getTimesFromPreviousTweet();
		}//コンストラクタの終わり

	// ゲッター---------------------------------------------------
		public function getPreviousDays(){
			return $this->previousDays;
		}
		public function getPreviousTimes(){
			// debug('PreviousTimes',$this->previousTimes);
			return $this->previousTimes;
		}
		// 過去ツイートの日数を取得するメソッド---------------------------------------------------------------------------
		function getDaysFromPreviousTweet(){
			for ($i = 0; $i < $_SESSION['rows_format']; $i++){ //フォーマットの行数まで
				if($_SESSION['replace_rows_day'][$i]){ //[]が含まれている行に対して処理を実行
					$pattern = '/[0-9０-９]+/u';//数字のみ
					preg_match($pattern, $this->explodeTweet()[$i], $daysFromPreviousTweet );
					// debug(($i+1).'行目に日付があり、日数は→',$daysFromPreviousTweet[0]);
					return (int)$daysFromPreviousTweet[0]; // 過去ツイートの報告日数
				}
			}
		}
		// 過去ツイートの日数を置換し、置換した行を返すメソッド-------------------------------------------------------------
		function replaceDaysFromPreviousTweet(){
			for ($i = 0; $i < $_SESSION['rows_format']; $i++){ //フォーマットの行数まで
				if($_SESSION['replace_rows_day'][$i]){ //[]が含まれている行に対して処理を実行
					// $pattern = '/[0-9０-９]+/u';//数字のみ
					// preg_match($pattern, $this->explodeTweet()[$i], $daysFromPreviousTweet );
					// debug(($i).'行目検索結果',$daysFromPreviousTweet[0]);
					// return (int)$daysFromPreviousTweet[0]; // 過去ツイートの報告日数
					$replacePattern = '/[0-9０-９]+/u';
					$replacement = '*+*';
					$replacedDaysPreviousTweet = preg_replace($replacePattern, $replacement, $this->explodeTweet()[$i], 1);

					// debug('replaceDaysFromPreviousTweet',$replacedDaysPreviousTweet);
					return $replacedDaysPreviousTweet;
				}
			}
		}

		// 過去ツイートを時間検索用に整形する------------------------------------------------------------
		function modifyPreviousTweet(){
			$row = array();
			for ($i=0; $i < $_SESSION['rows_format']; $i++) { 
				if($_SESSION['replace_rows_day'][$i]){
					$row[$i] = $this->replaceDaysFromPreviousTweet();
				}else{
					$row[$i] = $this->explodeTweet()[$i];
				}
			}
			// debug('modifyPreviousTweet',$row);
			return $row;
		}

		// 過去ツイートから時間を取得------------------------------------------------------------
		function getTimesFromPreviousTweet(){
			$timesFromPreviousTweet = array();
			for ($i = 0; $i < $_SESSION['rows_format']; $i++){
				if($_SESSION['replace_rows'][$i]){ //フォーマットに{}が含まれている行に対して処理を実行
					$pattern = '/[0-9０-９]+[..．:：0-9０-９hoursmin分時間\s]*[0-9０-９hoursmin分時間]*/u';
					preg_match_all($pattern, $this->modifyPreviousTweet()[$i], $timesFromPreviousTweetRow );
					foreach($timesFromPreviousTweetRow[0] as $val){
						$timesFromPreviousTweet[] .= $val;
					}
					// debug(($i+1).'行目検索結果',$latestTweetNumbers[0]);
					// debug('過去ついから{$i}行目の数値検索結果',$timesFromPreviousTweet[0]);
					// $replacePattern = '/[0-9０-９]+/u';
					// $replacement = '*';
					// $replacedFormat[$i] = preg_replace($replacePattern, $replacement, $timesFromPreviousTweet[0]); //*.*h **時間**分
				}
			}
			// debug('timesFromPreviousTweet',$timesFromPreviousTweet);
			return $timesFromPreviousTweet; // 配列で格納[$i]が順番　**時間**分　と入る
		}




		public function explodeTweet(){
			// １.最新ツイートを分割する この配列の[0]が１行目
			// debug( explode("\n", $this->getTweetText()[0] ) );
			return explode("\n", $this->getTweetText());//explode 指定の文字列で文字列を分割して配列にする
		}
		

		// ------------------------------------------------------------
		public function copyRowsFromLastTweet(){
			// debug('copyRowsFromLastTweetが読み込まれました');
			// フォーマットに---が含まれる行に対して実行する
			$copyLastTweet = array();
			for ($i = 1; $i < $_SESSION['rows_format']; $i++){ //$iは１から始める（２行目から） フォーマットの行数まで　１行目は日付なので
				//フォーマットの各行に---が含まれているものにフラグを立てる
				if($_SESSION['copy_rows'][$i]){ //---が含まれている行に対して処理を実行
					$copyLastTweet[$i] = $this->explodeTweet()[$i];
				}
			}
			// debug('コピー結果',$copyLastTweet);
			return $copyLastTweet;
		}

		// ------------------------------------------------------------
		public function calcTodayStudyDays(){
			//３.日数を計算する
			// ツイートする日付（時間は含まない）と最新ツイートの日付の差をとる
			$tmp1 = strtotime( date("Y-m-d") ); //これからツイートする日付
			$tmp2 = strtotime( date("Y-m-d", strtotime($this->getCreatedAt()) ) ); //最新ツイートの日付
			$diffDays = ($tmp1 - $tmp2)/(60*60*24); //UNIXタイムスタンプを日数に変換

			// 翌日0〜18時までのツイートは、前日とみなす
			// つまり18時〜24時までのツイートが当日分と扱われる
			// 最新ツイートの時刻を取得　まずは時間のみにし、intにキャストする　その後if
			$tmp3 = strtotime($this->getCreatedAt());
			$nowTweetHour = (int)(date("H", time() ) );
			$latestTweetHour = (int)(date("H", $tmp3));
			$tmp3 = 0;
			if($nowTweetHour < 18){ //ここの数字は、セッターで指定できるようにする
				$diffDays--;//今のツイート時刻が18時未満だったら、日数計算結果から１を引く
				$tmp3--;
			}
			if($latestTweetHour < 18){
				$diffDays++;//最新ツイート時刻が18時未満だったら、日数計算結果に１を足す
			}

			//４.日数を更新する　これで日数は完成
			if(!($this->getCreatedAt())){
				return 0;
			}
			
			switch ((int)$_SESSION['calc_option']) {
				case 1:
					return $this->getPreviousDays() + $diffDays;
					break;
				case 2:
					return $this->getPreviousDays() + 1;
					break;
				case 3:
					//起算日と今の日付の差
					$startDay =  strtotime($_SESSION['startdate'] ); //yyyy-mm-dd形式 をunixに変換してから足す
					// debug('starDay',$startDay);
					$days =  ($tmp1 - $startDay)/(60*60*24) + 1 + $tmp3;
					if(empty($startDay)){
						$days = 1;
					}
					if($days <= 0){
						$days = 1;
					}
					return $days;
				default:
					// debug('セッションの値が不正です');
					// debug('セッション',$_SESSION);
					return 0;
					break;
			}
			

		}

		// ------------------------------------------------------------
		public function searchNumbersFromLastTweet(){ // 全ついから時間を抜き出す
			// 全ついを正規表現で（*.* **hors**min *時間*分）検索
			// 前ついの時間として取得する

			$replacedFormat = array();
			for ($i = 1; $i < $_SESSION['rows_format']; $i++){ //$iは１から始める（２行目から） フォーマットの行数まで　１行目は日付なので
				//フォーマットの各行に{}が含まれているものにフラグを立てる
				// debug('replaceRows',$_SESSION['replace_rows'][$i]);
				// debug(($i+1).'行目は{}が含まれているか',$_SESSION['replace_rows'][$i]);
				if($_SESSION['replace_rows'][$i]){ //{}が含まれている行に対して処理を実行
					$pattern = '/[0-9０-９]+[..．:：0-9０-９hoursmin分時間\s]*[0-9０-９hoursmin分時間]*/u'; //このままでは日数も検索されてしまう　日数が含まれる行の最初の検索結果＝日数　とし、それは除外するか
					// 日数は別メソッドで先に取得しておき、数字を置換しておく！！！！！！
					preg_match_all($pattern, $this->explodeTweet()[$i], $latestTweetNumbers );
					// debug(($i+1).'行目検索結果',$latestTweetNumbers[0]);
					// debug('過去ついから{$i}行目の数値検索結果',$latestTweetNumbers[0]);
					$replacePattern = '/[0-9０-９]+/u';
					$replacement = '*';
					$replacedFormat[$i] = preg_replace($replacePattern, $replacement, $latestTweetNumbers[0]); //*.*h **時間**分
					foreach($replacedFormat as $val){
						foreach($val as $val1){
						$replacedFormatForSearch[$i] = '{'.$val1.'}'; // {*.*h **時間**分}
						// debug(($i+1).'行目置換結果',$replacedFormat[$i] );
						}
					}
				}
			}
		}


// ------------------------------------------------------------
		public function getStudyHoursToday(){
			$pattern = '/[0-9０-９..．]+/u'; // パターン。末尾のuを忘れずに。
			preg_match($pattern, $this->explodeTweet()[1], $matches );
			// debug('２行目'.$this->explodeTweet()[1]);
			// debug('前の当日学習時間 : '.$matches[0]);
			return $matches[0]; //連想配列で返ってくる　指定キー全てに一致するものが0番	
		}

		// ------------------------------------------------------------
		public function getStudyHoursTotal(){
			$pattern = '/[0-9０-９..．]+/u'; // パターン。末尾のuを忘れずに。
			preg_match($pattern, $this->explodeTweet()[2], $matches );
			return $matches[0]; //連想配列で返ってくる　指定キー全てに一致するものが0番	
		}

	}//PreciousTweetsクラスの終わり


	class TodayTweet extends Tweets{
		public function __construct($studyDaysTotal, $hashTags){
			$this->studyDaysTotal = $studyDaysTotal;
			$this->hashTags = $hashTags;
		}
	}
	//過去ツイートのインスタンス生成
	//ツイートの中から対象文字列を含むものを検索
	//検索するのは$tweet[$i]['text']の中のみ
	for($i = 0; $i < $count; $i++){
		if (!empty($tweets[$i])){
			$tweetText = $tweets[$i]['full_text'];
			$created_at = date('Y-m-d H:i', strtotime($tweets[$i]['created_at']));
			$flg1 = array(); //ツイートが正規表現にマッチすれば立つ　各ワードごと
			$flg2 = 0; //flg1がfalseならインクリメントする 全部のワード検索が終わった後、flg2が0なら＝全部Trueなら　それが報告ツイート
			foreach ($_SESSION['pattern'] as $val){
				$flg1 = preg_match('/'.$val.'/i', $tweets[$i]['full_text']);
				if(!$flg1){
					$flg2++;
				}
			}
			if($flg2 === 0){
				$tweetAnalsys[] = $tweets[$i]; 
				//ハッシュタグ
				$rowHashTags = $tweets[$i]['entities']['hashtags'];
				$hashTags[$i] = array();
				foreach($rowHashTags as $val){
					$hashTags[$i][] = '#'.$val['text'];
					// debug($val['text']);
					// debug($hashTags[$i]);
				}
				// debug($hashTags[$i]);
				$tweet[] = new PreviousTweets($tweetText,$created_at,$hashTags[$i]);
			}
		}
	}

	// 新規ツイートのインスタンス生成
	if($tweet){
		// $todayTweet = new TodayTweet($tweet[0]->calcTodayStudyDays(),$tweet[0]->getStudyHoursTotal(),'');

			//テンプレ書式
//クラスでやったほうがいいんだろうけど、とりあえずテンプレを作ってみる
// テンプレを行分けずに、{}の出現番号で区別する？
// $todayTweetRows = array();
 
//$replacedTweetFormat はフォーマットの数字を置換しただけ
// []の数字を提案日数に置換する　[]は残したまま　[89]　みたいにする
	$replacePattern = '/\[[^]]*\]+/u';
	$replacement = '['.$tweet[0]->calcTodayStudyDays().']';
	$replacedTweetFormat = preg_replace($replacePattern, $replacement, $_SESSION['replaced_tweet_format']);
	
	// *---*を前ツイのコピーに置換する
	$replaceRowFromLastTweet = $tweet[0]->copyRowsFromLastTweet();
	foreach($replaceRowFromLastTweet as $val){
		if($val){
			$replacePattern = '/\*---\*+/u';
			$replacement = $val;
			// debug('コピーするもの',$val);
			$replacedTweetFormat = preg_replace($replacePattern, $replacement, $replacedTweetFormat, 1);
		}
	}
			$template = <<<EOT
{$replacedTweetFormat}



{$_SESSION['hashTags']}
EOT;
// debug('テンプレ',$template);

	}else{
		// debug('フォーマットに一致するツイートがありません');
		$tweet[0] = new PreviousTweets('フォーマットに一致するツイートが見つかりませんでした','','','','','');

			$template = <<<EOT
{$_SESSION['replaced_tweet_format']}



{$_SESSION['hashTags']}
EOT;
// debug('テンプレ',$template);
}

}//ユーザー判定の終わり


?>

<?php require('head.php');?>

<body>

<?php require('header.php')?>


<div class="container">
	
	<div class="form-row d-flex">

		<div class="col-sm-4">
			<div class="form-group row">
				<label for="">　 　 　 　 　</label><br>
				<label class="col-sm-3">本日の値</label>
				<label style="display:none" class="col-sm-3 d-sm-inline-block">前回の値</label>
			</div>
			<div class="form-group row">
				<label for="" class="col-sm-3">Day</label>
				<input type="text" class="js-replace-day form-control col-sm-3"
				placeholder = "<?php echo ( !empty( ( $tweet[0]->calcTodayStudyDays() ) ) )? htmlspecialchars($tweet[0]->calcTodayStudyDays() ): '' ; ?>"
				value       = "<?php echo ( !empty( ( $tweet[0]->calcTodayStudyDays() ) ) )? htmlspecialchars($tweet[0]->calcTodayStudyDays() ): '' ; ?>" >
				<input disabled style="display:none" class="form-control col-sm-3 d-sm-inline-block" type="text"
				placeholder = "<?php echo ( !empty( ( $tweet[0]->getPreviousDays() ) ) )? htmlspecialchars($tweet[0]->getPreviousDays() ): '' ; ?>"
				value       = "<?php echo ( !empty($todayTweet) )? htmlspecialchars( $todayTweet->getStudyDaysTotal() ): '' ; ?>">
			</div>
			<!-- {}の数だけinput boxを生成　[]は１つ固定 -->
				<?php for($i = 0 ; $i < $_SESSION['number_count'];$i++):?>
				<div class="form-group row">
					<label for="" class="col-sm-3">Time-<?php echo $i+1?></label>
					<input type="text" value="" class="js-replace-time form-control col-sm-3">
					<input type="text" disabled style="display:none" class="form-control col-sm-3 d-sm-inline-block" value="<?php echo !empty( $tweet[0]->getPreviousTimes()[$i] )? htmlspecialchars( $tweet[0]->getPreviousTimes()[$i] ):'' ?>">
				</div>
		<?php endfor;?>

		<form action="https://twitter.com/compose/tweet" method="get">
			<textarea class="js-postTweet" style="display:none;" name="text" id="" cols="30" rows="10"></textarea>
			<div class="form-check mb-2">
				<input class="form-check-input js-addurl" name="addurl" type="checkbox" id="check1a" value="1">
				<label class="form-check-label" for="check1a">作成者を応援する<br><small>(本文最下部にURLが表示されます)</small></label>
			</div>
			<button class="btn btn-primary col-sm-10"><i class="fab fa-twitter"></i>　ツイート画面へ</button>
			<!-- <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> -->
		</form>
		
		<div class="mt-4 mb-4">
    	<small><a target="_blank" href="https://ofuse.me/#users/13507">もっと応援してくれる人はこちら</a></small>
		</div>

		</div>
		<!-- ツイート提案 -->
		<section class="form-group col-sm-4">
			<label>報告ツイート生成</label><br>
			<label> <?php echo date('Y-m-d H:i', time())?></label>
			<textarea class="js-proposedTweet form-control" name="" id="" cols="30" rows="15"><?php echo htmlspecialchars( $template )?></textarea>
			<span class="form-text text-muted"><small>ctrl + command + space で絵文字が入力できます</small></span>
		</section>
		
		<section class="form-group col-sm-4">
			<!-- 過去ツイート -->
			<!-- 高さを無駄のないように自動調整させる -->
			<label>直近の報告ツイート</label><br>
			<label> <?php echo htmlspecialchars($tweet[0]->getCreatedAt()) ?> </label>
			<textarea class="form-control" disabled name="" id="" cols="30" rows="15"><?php echo htmlspecialchars($tweet[0]->getTweetText())?></textarea>

		</section>
	</div>

</div>
<!-- 過去ツイート -->

<!-- フッター -->

<?php require('footer.php'); ?>
<script src="js/index.js"></script>
</body>
</html>