<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
<div class="container">
  <a class="navbar-brand" href="index.php">Report Supporter <small>for Twitter</small></a>
  <span class="navbar-text" style="font-size:0.5rem;"><?php echo $_SESSION['user'].' さん 今日もお疲れ様でした！'?></span>
  <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#Navber" aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse justify-content-end" id="Navber">
	<div class="navbar-nav">
        <a class="nav-item nav-link" href="resister.php">フォーマット登録 <span class="sr-only">(現位置)</span></a>
        <a class="nav-item nav-link" href="calcday.php">日数計算方法変更 <span class="sr-only">(現位置)</span></a>
        <a class="nav-item nav-link" href="readme.php">Readme <span class="sr-only">(現位置)</span></a>
	</div>
  </div> <!---/.navbar-collapse -->
  </div> 
  </nav>