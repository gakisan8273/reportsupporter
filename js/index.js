$(function(){
  // console.log('jQuery読み込み完了');
  postTweet = '';
  postTweet = $('.js-proposedTweet').val().replace(/[\{\}\[\]]/g,'');
  $('.js-postTweet').val(postTweet);

  $('.js-proposedTweet').on("input",function(){
    postTweet = '';
    postTweet = $('.js-proposedTweet').val().replace(/[\{\}\[\]]/g,'');
    $('.js-postTweet').val(postTweet);
  });
  
  // 時間入力欄に入力すると、フォーマットの{}の中身を入力したものに置換する
  $('.js-replace-time').on("keyup",function(){
    let inputText = $(this).val(); //入力した文字
    let inputTextboxIndex = $('.js-replace-time').index(this);//テキストボックスのインデックス
    let proposedTweet = $('.js-proposedTweet').val();
    
      // {が何個あるか探す (count - 1 ) +1 = count 個ある
      let count = 0;
      let place = [];
      place[count] = proposedTweet.indexOf('{');
      while ( place[count]  > 0 ) {
         count++;
         place[count] = proposedTweet.indexOf('{', place[count-1] + 1);
        //  console.log(place[count]);
      }
      // console.log(place);

    //フォーマットの{任意文字列}を{inputText}に置換する　インデックスが一致するもの
    // インデックスが１（２番目のテキストボックス）なら、検索場所を2番目の{の位置から始める
    // 開始位置を指定できる置換関数はない　文字列を分割するといいかも　2番目の{の位置＝place[1]の前後で文字列を２つにわけ、後ろの文字列を置換
    // その後前の文字列と置換した後ろの文字列を結合する
    // そしたら、{の位置の再計算が必要になる
    let dividedProposedTweet = [];
    if(place[inputTextboxIndex] === 0){
      replacedTweet = proposedTweet.replace(/\{[^}]*\}+/u,'{'+ inputText + '}');
    }else{
      dividedProposedTweet[0] = proposedTweet.slice(0,place[inputTextboxIndex]); //2番目の{ 含まず、それの手前まで
      dividedProposedTweet[1] = proposedTweet.slice(place[inputTextboxIndex]); //2番目の{含む、最後まで
      // console.log(dividedProposedTweet[0]);
      // console.log(dividedProposedTweet[1]);
      replacedTweet = dividedProposedTweet[1].replace(/\{[^}]*\}+/u,'{'+ inputText + '}');
      replacedTweet = dividedProposedTweet[0] + replacedTweet;
    }

    $('.js-proposedTweet').val(replacedTweet);
    
    //[]と{}を削除したものをツイート用に準
    postTweet = replacedTweet.replace(/[\{\}\[\]]/g,'');
    $('.js-postTweet').val(postTweet);
    // console.log(postTweet);
  });

  // 日付入力欄に入力すると、フォーマットの[]の中身を入力したものに置換する
  $('.js-replace-day').on("keyup",function(){
    let inputText = $(this).val(); //入力した文字
    // let inputTextboxIndex = $('.js-replace-time').index(this);//テキストボックスのインデックス
    let proposedTweet = $('.js-proposedTweet').val();

    //フォーマットの{任意文字列}を{inputText}に置換する　インデックスが一致するもの
    //{任意文字列}を/\[[^]]*\]+/u
    replacedTweet = proposedTweet.replace(/\[.*\]+/u,'['+ inputText + ']');
    $('.js-proposedTweet').val(replacedTweet);
    // console.log(replacedTweet);

    //[]と{}を削除したものをツイート用に準
    postTweet = replacedTweet.replace(/[\{\}\[\]]/g,'');
    $('.js-postTweet').val(postTweet);
  });

  //チェックボックスがONになったら、ツイートの最後にURLを付与
  $(".js-addurl").on("change",function(){
    // console.log('チェックボックス変化');
    word = 'Posted by:'
    url = 'http://gakisan8273.com/reportsupporter/'
    if($(this).prop('checked')){
      // console.log('チェックボックスON');
      addedTweet = $('.js-proposedTweet').val() + '\r\n' + word + url;
      $('.js-proposedTweet').val(addedTweet);

      postTweet = addedTweet.replace(/[\{\}\[\]]/g,'');
      $('.js-postTweet').val(postTweet);
      // console.log($('.js-postTweet').val());
    }else{
      //OFFになったら、URLを削除する
      // console.log('チェックボックスOFF');
      pattern = word + url;
      // console.log('パターン');
      // console.log(pattern);
      addedTweet = $('.js-proposedTweet').val().replace(pattern,'');
      $('.js-proposedTweet').val(addedTweet);

      postTweet = addedTweet.replace(/[\{\}\[\]]/g,'');
      $('.js-postTweet').val(postTweet);
      // console.log($('.js-postTweet').val());
      // console.log($('.js-postTweet').val());
    }

  });


  $ftr = $('#footer');
  //window高さを取得し、フッターの開始位置と比較する
  // ウィンドウ高さの方が高い→画面中央よりにフッターが表示されている　のであれば、フッター開始位置を最下部＋フッター高さにする
  var windowHeight = window.innerHeight;
  var ftrHeight = $ftr.innerHeight();
  var ftrPosition = $ftr.offset().top;
  // console.log('ウィンドウ高さ' + windowHeight);
  // console.log('フッター自身の高さ' + ftrHeight);
  // console.log('フッターの初期位置' + ftrPosition);
  if(ftrPosition + ftrHeight < windowHeight){
    $ftr.attr({'style': 'top:' + ( (windowHeight - ftrHeight)  - ftrPosition) + 'px'});
    // フッターの今のポシション＋α　にしたい
    // ウィンドウの最下部-フッター高さ　＝　フッター開始位置
    // 今のフッター高さ
    // フッター開始位置　ー　今のフッター高さ　をtopに指定し、relativeのままでいく
    // console.log('フッター高さ変更');
  }else{
    // console.log('フッター高さ変更なし');
  }  

});