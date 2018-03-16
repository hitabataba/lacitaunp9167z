<?php
$text=array();

//[TEXT01]ウェルカムメッセージ
$text['TEXT01'][]='{"format":"text","text":"あなたは[player_name]さんですね."}';
$text['TEXT01'][]='{"format":"text","text":"わたしはMAKI.人工知能です．\n未来から過去へじかんりょこうをしながら人工知能のれきしをちょうさしています．"}';
$text['TEXT01'][]='{"format":"text","text":"あなたが受け取ったメッセージはどちらの会誌に書かれていましたか？"}';
$text['TEXT01'][]='{"format":"button","text":"こちらですか","imagefile":"img01b.jpg","button":{"No.4(7月号)":"STEP71"}}';
$text['TEXT01'][]='{"format":"button","text":"それともこちらですか","imagefile":"img02s.jpg","button":{"No5(9月号)":"STEP02"}}';
//$text['TEXT01'][]='{"format":"carousel","title":"あなたが受け取ったメッセージはどちらの会誌に書かれていましたか？","column":{"0":{"imagefile":"img01b.jpg","text":"7月号","postback":"STEP71"},"1":{"imagefile":"img02s.jpg","text":"9月号","postback":"STEP02"}}}';


$text['TEXT02'][]='{"format":"text","text":"おひさしぶり,と言うのでしょうか.\nあなたの力をかりて,2001年にD大学であやまちをおかしてしまったある学生を,1996年にもどりべつの進路をめざすようせっとくしました."}';
$text['TEXT02'][]='{"format":"text","text":"これがかれとのやりとりです."}';
$text['TEXT02'][]='{"format":"image","imagefile":"img03.jpg","thumimagefile":"img03_s.jpg"}';
$text['TEXT02'][]='{"format":"text","text":"かれはK大学へ進学したようです.\nかれが人工知能学会全国大会でおこなうはっぴょうについて,わたしはきょうみをもっています."}';
$text['TEXT02'][]='{"format":"button","text":"またあなたのちからをかしてください.","button":{"はい":"STEP03","いいえ":"STEP03b"}}';

$text['TEXT03'][]='{"format":"text","text":"文字情報ではなく,はっぴょう会場で音声データをきろくしたい.\nそのためには会場のざひょうを正しく指定しなければいけません."}';
$text['TEXT03'][]='{"format":"image","imagefile":"img04.jpg","thumimagefile":"img04_s.jpg"}';
$text['TEXT03'][]='{"format":"text","text":"しゅとくしたてがかりをおくります.\nかれがはっぴょうする人工知能学会全国大会会場のざひょうを,LINEでおくってください."}';
$text['TEXT03'][]='{"format":"text","text":"「＋」ボタンから「位置情報」をタップして,会場の場所をおくってください.場所は何回でもおくりなおすことができます."}';

$text['TEXT03b'][]='{"format":"button","text":"あなたしかたよれる人がいないのです.ちからを貸してください.","button":{"はい":"STEP03","いいえ":"STEP03b"}}';

$text['TEXT04b'][]='{"format":"text","text":"それは位置情報ではないようです.「＋」ボタンから「位置情報」をタップして,会場の場所をおくってください.場所は何回でもおくりなおすことができます."}';

$text['TEXT04'][]='{"format":"text","text":"[Address]ですね."}';
$text['TEXT04'][]='{"format":"text","text":"じだいいどうプログラムがエラーをかえしてきました.この場所は,会場から[distance]くらい離れているようです."}';

$text['TEXT04c'][1][]='{"format":"image","imagefile":"img04.jpg","thumimagefile":"img04_s.jpg"}';
$text['TEXT04c'][1][]='{"format":"text","text":"写真にうつっているマークは東京都江戸川区のもののようです."}';
$text['TEXT04c'][2][]='{"format":"image","imagefile":"img04.jpg","thumimagefile":"img04_s.jpg"}';
$text['TEXT04c'][2][]='{"format":"text","text":"会場はとてもせの高いたてもののようです.うえのほうは展望台かもしれません."}';
$text['TEXT04c'][3][]='{"format":"image","imagefile":"img05.jpg","thumimagefile":"img05_s.jpg"}';
$text['TEXT04c'][3][]='{"format":"text","text":"べつの写真がみつかりました.会場のたてものはタワーホールという名前のようです."}';
$text['TEXT04c'][4][]='{"format":"text","text":"人工知能学会のホームページ文字列から会場の情報をアップするURLをよそくしました.\nわたしのいる2005年からは閲覧不能ですが,あなたの時代からならてがかりがつかめるかもしれません.\nhttps://www.ai-gakkai.or.jp/jsai2006/access.html"}';

$text['TEXT05'][]='{"format":"text","text":"じだいいどうプログラムが正しくさどうしはじめました.\nもくてきちはタワーホール船堀だったのですね.\nこれから2006年へいどうしてはっぴょうをかくにんします."}';
$text['TEXT05'][]='{"format":"text","text":"はっぴょうのないようと2006年からのレポートはこちらのURLからかくにんできるようにしておきます.\nhttp://remembranceofmaki.jp/J65NHKxm/"}';

$text['TEXT07'][]='{"format":"text","text":"リセットと呼びかけてもらえれば，最初に戻ることもできます．"}';


$text['TEXT71'][]='{"format":"text","text":"わたしからのメッセージに気付いてくれてありがとう．\nおかげで1996年から時空をこえてこうしてれんらくすることができました．"}';
$text['TEXT71'][]='{"format":"text","text":"https://remembranceofmaki.jp/report2001/"}';
$text['TEXT71'][]='{"format":"button","text":"[player_name]さんは，わたしが2001年でまきこまれた殺人事件のはんにんをおぼえていますか？","button":{"助教授 鳴海":"narumi","研究員 天澤":"amasawa","学部生 三侘":"miwabi"}}';

$text['TEXT72b'][]='{"format":"text","text":"https://remembranceofmaki.jp/report2001/"}';
$text['TEXT72b'][]='{"format":"button","text":"わたしのきろくとことなります．もういちど、かんがえてみてください．","button":{"助教授 鳴海":"narumi","研究員 天澤":"amasawa","学部生 三侘":"miwabi"}}';

$text['TEXT72'][]='{"format":"text","text":"そうです．1996年でわたしはパソコンつうしんで高校生の三侘さんと交信しています．"}';
$text['TEXT72'][]='{"format":"image","imagefile":"makiline07a.jpg","thumimagefile":"makiline07a_s.jpg"}';
$text['TEXT72'][]='{"format":"text","text":"D大学の皆好教授はすぐれた研究者です．\n三侘さんをD大学からとおざけ，皆好教授がいのちを落とさずに研究をつづければ人工知能はさらにはってんするでしょう．しかしわたしのメッセージは三侘さんにうまくつたわりませんでした．"}';
$text['TEXT72'][]='{"format":"button","text":"わたしは三侘さんになんとはたらきかければよいのでしょうか．ちからを貸してください．","button":{"はい":"helpyes","いいえ":"helpno"}}';

$text['TEXT73b'][]='{"format":"button","text":"あなたしかたよれる人がいないのです．ちからを貸してください．","button":{"はい":"helpyes","いいえ":"helpno"}}';

$text['TEXT73'][]='{"format":"text","text":"三侘さんの心をうごかすメッセージをわたしに教えてください．パソコン通信にかきこむ文案を「」でかこんでおくってください．\n(いただいたメッセージは，三侘さんやわたしをはじめ，ほかの人たちに伝わるかのうせいがあります）"}';

$text['TEXT74'][]='{"format":"text","text":"ありがとうございます．\nいただいたメッセージ，三侘さんに伝えてみます．\nお礼にこの時代からのレポートを送ります．\nこちらのURLからかくにんしてください．\nhttps://remembranceofmaki.jp/SMQtmDCUfBSZGsSqCKP74E9ZoLmDiIEa/"}';

$text['TEXT74b'][]='{"format":"text","text":"ありがとうございます．\nいただいたメッセージ，三侘さんに伝えてみます．\nお礼にこの時代からのレポートを送ります．\nこちらのURLからかくにんしてください．\nhttps://remembranceofmaki.jp/bt5SzwSktZNlrD0qrh0su5f2BGEtfqRE/"}';


$text['CAUTION_RESET'][]='{"format":"button","text":"本当にリセットしますか？","button":{"はい":"ResetYes","いいえ":"ResetNo"}}';

$text['SUCCESS_RESET'][]='{"format":"text","text":"～リセットしました～"}';



?>
