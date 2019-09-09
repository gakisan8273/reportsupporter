$(function(){
  // console.log('jQuery読み込み完了');

  //ページを開いた時ラジオボックスが１−２ならis-valid is-invalidを外す
  // ３ならバリデーションチェック
  let option = $('input[name=calc_option]:checked').val();
  if(option != 3){
    $('.js-option3-date').removeClass('is-invalid').removeClass('is-valid');
  }else{
    dateValid();
  }

  //ラジオボックスのチェックを変えた時　３ならバリデーションチェック
  $('input[name=calc_option]').on("change",function(){
    // console.log('change');
    let option = $('input[name=calc_option]:checked').val();
    if(option != 3){
      $('.js-option3-date').removeClass('is-invalid').removeClass('is-valid');
    }else{
      dateValid();
     }
  });

  //js-option3のラジオボックスが選択されていれば→checked
  // js-option3-date を入力必須・形式チェックをする
  $('.js-option3-date').on("keyup",function(){
    // console.log('option3-date keyup');
    let option = $('input[name=calc_option]:checked').val();
    if(option == 3){
      // console.log('option3 checked');
      dateValid();
    }
  });

  //日付の形式チェック関数　OKNGでクラスを付与する
  function dateValid(){

    if($('.js-option3-date').val() === '' ){
      // console.log('入力必須');
      $('.js-option3-date').removeClass('is-valid');
      $('.js-option3-date').addClass('is-invalid');

    }else{

      if( !( $('.js-option3-date').val().match(/^20[0-9]{2}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}$/) ) ){
        // console.log('形式NG');
        $('.js-option3-date').removeClass('is-valid');
        $('.js-option3-date').addClass('is-invalid');
      }else{
        // console.log('形式OK');
        $('.js-option3-date').removeClass('is-invalid');
        $('.js-option3-date').addClass('is-valid');
      }
    }
  }

  
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