$(function(){
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

  //選択範囲の前後に{} または[] を挿入する

$('.js-insert-day').on("click",function(){
  var textarea = document.getElementsByClassName('js-format')[0];
  var pos_start = textarea.selectionStart;
  var pos_end = textarea.selectionEnd;
  var val = textarea.value;
  var range = val.slice(pos_start, pos_end);
  var beforeNode = val.slice(0, pos_start);
  var afterNode = val.slice(pos_end);
  var insertNode = "[" + range + "]";
  textarea.value = beforeNode + insertNode + afterNode;
  });

  $('.js-insert-time').on("click",function(){
    var textarea = document.getElementsByClassName('js-format')[0];
    var pos_start = textarea.selectionStart;
    var pos_end = textarea.selectionEnd;
    var val = textarea.value;
    var range = val.slice(pos_start, pos_end);
    var beforeNode = val.slice(0, pos_start);
    var afterNode = val.slice(pos_end);
    var insertNode = "{" + range + "}";
    textarea.value = beforeNode + insertNode + afterNode;
  });

  $('.js-insert-copy').on("click",function(){
    var textarea = document.getElementsByClassName('js-format')[0];
    var pos_start = textarea.selectionStart;
    var pos_end = textarea.selectionEnd;
    var val = textarea.value;
    var range = val.slice(pos_start, pos_end);
    var beforeNode = val.slice(0, pos_start);
    var afterNode = val.slice(pos_end);
    var insertNode = "*---*" + range;
    textarea.value = beforeNode + insertNode + afterNode;
  });

});