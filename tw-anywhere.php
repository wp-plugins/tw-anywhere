<?php
/* これは文字化け防止のための日本語文字列です。
   このソースファイルは UTF-8 で保存されています。
   Above is a Japanese strings to avoid charset mis-understanding.
   This source file is saved with UTF-8. */
/*
Plugin Name: Tw Anyware comment system
Plugin URI: http://vcsearch.web-service-api.jp/
Description: Add Twitter anyware API,connect and tweet,follow.
Author: wackey
Version: 1.34
Author URI: http://musilog.net/
*/

/*  Copyright 2009-2010 wackey

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// ヘッダにJavascript読み込み
function add_anywhere_script() {
$tw_anywhere_api_key= get_option('tw_anywhere_api_key');
echo '<script src="http://platform.twitter.com/anywhere.js?id='.stripslashes($tw_anywhere_api_key).'&v=1"></script>';
}

// 本文の下部分にtwitter機能を搭載する場所をdivで指定
function add_anyware_area($content) {

if (is_single()) {
$tw_anywhere_commenttitle= get_option('tw_anywhere_commenttitle');
if ($tw_anywhere_commenttitle=="") {$tw_anywhere_commenttitle="Twitter Comment";}
$content .='<div id="twitterConnectButton"></div>
<div id="twitterSignOut"></div><br />
<div id="twitterFollowButton"></div>
<div id="twitterUserInfo"></div>
<div id="twitterTweetBox"></div>
<div style="margin:0 0 10px 0;padding:10px;border:1px solid #BDDCAD;background:#EDFFDC;-moz-border-radius:10px;-webkit-border-radius:10px;">
<h4 style="margin:0 0 5px 0;padding:0;">'.$tw_anywhere_commenttitle.'<span id="topsy_counter"></span></h4>
<div id="topsy_trackbacks"></div>
</div>';
}
// Comment by tospy API thx.yager http://creazy.net/2009/12/topsy_api_twitter_blogparts.html
return $content;

}

// フッターにjavascriptでいろいろ
function add_footer_script() {
$tw_anywhere_username= get_option('tw_anywhere_username');
$tw_anywhere_commenttext= get_option('tw_anywhere_commenttext');
if ($tw_anywhere_commenttext=="") {$tw_anywhere_commenttext="Please post here like the comment.";}
$tw_anywhere_bitlyusername= get_option('tw_anywhere_bitlyusername');
$tw_anywhere_bitlyapikey= get_option('tw_anywhere_bitlyapikey');
$permalink = get_permalink();
// bitlyアカウントが設定されているかどうか確認
if (!$tw_anywhere_bitlyapikey=="") {
$bitlyurl = "http://api.bit.ly/shorten?version=2.0.1&format=xml&login=$tw_anywhere_bitlyusername&apiKey=$tw_anywhere_bitlyapikey&longUrl=$permalink";
$xml = simplexml_load_file ($bitlyurl);
$shortlink = $xml->results->nodeKeyVal->shortCNAMEUrl;
}
?>
<script type="text/javascript">
// thx.yager http://creazy.net/2010/04/twitter_anywhere.html
var permalink = '<?php echo $permalink; ?>';

/**
 * コネクト済みの場合の処理
 */
function anywhereConnected(twitter) {
    // コネクト済の場合はユーザー情報が取得できる
    // データの呼び出しは、user.data('データ名')
    var user = twitter.currentUser;

    // コネクトユーザー情報の表示
<?php if (get_option('tw_your_profile_show')=="1") { ?>
    document.getElementById('twitterUserInfo').innerHTML
        = '<img src="'+user.data('profile_image_url')+'" alt="'+user.data('screen_name')+'" /><br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'" target="_blank">'+user.data('name')+'</a><br />'
        + '<b>現在地</b> '+user.data('location')+'<br />'
        + '<b>Web</b> <a href="'+user.data('url')+'" target="_blank">'+user.data('url')+'</a><br />'
        + '<b>自己紹介</b> '+user.data('description')+'<br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'/following" target="_blank">'+user.data('friends_count')+'</a>following<br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'/followers" target="_blank">'+user.data('followers_count')+'</a>followers<br />';
<?php } ?>

    // サインアウトボタンの表示
    document.getElementById('twitterSignOut').innerHTML
        = ' (<a href="javascript:twttr.anywhere.signOut();">Sign out</a>)';

    // フォローボタンの表示
    twitter('#twitterFollowButton').followButton("<?php echo stripslashes($tw_anywhere_username); ?>");

    // コメント欄の表示
    twitter('#twitterTweetBox').tweetBox({
        counter: true,
        width: 500,
        height: 50,
        label: "Twtter Post with this URL",
        defaultContent: "@<?php echo stripslashes($tw_anywhere_username); ?> <?php echo stripslashes($tw_anywhere_commenttext); ?> " + <?php if (!$tw_anywhere_bitlyapikey=="") {echo '"'.$shortlink.'"';} else {echo "permalink";} ?>,
	onTweet: function() {
        // Topsy APIを更新
        script = d.createElement('script');
        script.type = 'text/javascript';
        script.src  = 'http://otter.topsy.com/trackbacks.js?callback=topsyCallback&url='+encodeURIComponent(location.href);
        d.getElementsByTagName('head')[0].appendChild(script);
    }

    });
}
/**
 * コネクトボタンを設置する処理
 */
function anywhereConnect(twitter) {
    twitter("#twitterConnectButton").connectButton({
        // サイズ指定 {small | medium | large | xlarge}
        size: "large",
        // コネクト後の処理
        authComplete: function(loggedInUser) {
            twttr.anywhere(anywhereConnected);
        },
        // サインアウト後の処理
        signOut: function() {
            location.reload(true)
        }
    });
}

/**
 * @Anywhere を初期化
 */
twttr.anywhere(function(twitter){
    anywhereConnect(twitter);

    // Connect OK!
    if (twitter.isConnected()) {
        anywhereConnected(twitter);
    }
    // Connect NG!
    else {
        // Do something to connect.
    };
});
</script>
<script type="text/javascript">
function topsyCallback(json) {
    res = json.response;
    if ( !res.total ) {
        return false;
    }
    html = '（' + res.total + ' tweets）';
    if ( document.getElementById('topsy_counter') ) {
        document.getElementById('topsy_counter').innerHTML = html;
    }
    html = '<ul style="list-style:none;margin:0 0 5px 0;padding:0;">';
    for ( var i=0; i<res.list.length; i++ ) {
        tweet     = res.list[i];
        thumb     = tweet.author.photo_url.replace(/(normal)\.([a-z]{3,4})$/i,'mini.$2');
        author_id = tweet.author.url.replace('http://twitter.com/','');
        html
            += '<li style="margin:0;padding:1px;font:11px/16px sans-serif;color:#333;white-space:pre;overflow:hidden;">'
            +  '<a href="'+tweet.author.url+'" target="_blank">'
            +  '<img src="'+thumb+'" alt="'+tweet.author.name+'" style="border:0;vertical-align:middle;width:24px;height:24px;" />'
            +  '</a> '
            +  '<a href="'+tweet.author.url+'" target="_blank" style="color:#0084B4;">'
            +  author_id
            +  '</a> '
            +  tweet.content.replace(/(\r\n|\r|\n)/g,'')
            +  '</li>';
    }
    html += '</ul>';
    if ( res.total > 10 ) {
        html
            += '<div>'
            +  '<a href="'+res.topsy_trackback_url+'" target="_blank" style="display:inline-block;margin:0;padding:5px;font:14px/16px sans-serif;color:#0084B4;text-decoration:none;border:1px solid #CCC;background:#EEE;-moz-border-radius:5px;-webkit-border-radius:5px;">'
            +  'more'
            +  '</a>'
            +  '</div>';
    }
    if ( document.getElementById('topsy_trackbacks') ) {
        document.getElementById('topsy_trackbacks').innerHTML = html;
    }
}

script = document.createElement('script');
script.type = 'text/javascript';
script.src  = 'http://otter.topsy.com/trackbacks.js?callback=topsyCallback&url='+encodeURIComponent(location.href);
document.getElementsByTagName('head')[0].appendChild(script);
</script>
<?php
}

// 管理画面の作成

// 管理画面メニュー作成関数
function tw_anywhere_menu() {
add_options_page('Tw Anyware Options', 'Tw Anyware', 8, __FILE__, 'tw_anywhere_options');
}


// 管理画面描画
function tw_anywhere_options() {
// ポストされた値の入力チェックと書き込み
if (isset($_POST['update_option'])) {
check_admin_referer('tw-anywhere-options');
update_option('tw_anywhere_api_key', $_POST['tw_anywhere_api_key']);
update_option('tw_anywhere_username', $_POST['tw_anywhere_username']);
update_option('tw_anywhere_commenttitle', $_POST['tw_anywhere_commenttitle']);
update_option('tw_anywhere_commenttext', $_POST['tw_anywhere_commenttext']);
update_option('tw_your_profile_show', $_POST['tw_your_profile_show']);
update_option('tw_anywhere_bitlyusername', $_POST['tw_anywhere_bitlyusername']);
update_option('tw_anywhere_bitlyapikey', $_POST['tw_anywhere_bitlyapikey']);
update_option('tw_anyware_freearea', $_POST['tw_anyware_freearea']);
//$this->upate_options(); ?>
<div class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p>
</div> <?php }
$tw_anywhere_api_key= get_option('tw_anywhere_api_key');
$tw_anywhere_username= get_option('tw_anywhere_username');
$tw_anywhere_commenttitle= get_option('tw_anywhere_commenttitle');
$tw_anywhere_commenttext= get_option('tw_anywhere_commenttext');
$tw_your_profile_show= get_option('tw_your_profile_show');
$tw_anywhere_bitlyusername= get_option('tw_anywhere_bitlyusername');
$tw_anywhere_bitlyapikey= get_option('tw_anywhere_bitlyapikey');
$tw_anyware_freearea= get_option('tw_anyware_freearea');
?>

<div class="wrap">
<h2>Tw Anyware Settings</h2>
<form name="form" method="post" action="">
<input type="hidden" name="action" value="update" />
<?php wp_nonce_field('tw-anywhere-options'); ?>

<table class="form-table"><tbody>
<tr>
<th><label for="tw_anywhere_api_key"><?php
_e('Twitter Anywhere Application API key', 'tw_anywhere_api'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_api_key"
id="tw_anywhere_api_key" value="<?php
echo attribute_escape($tw_anywhere_api_key); ?>" /></td>
</tr>

<tr>
<th><label for="tw_anywhere_username"><?php
_e('your twitter username', 'tw_anywhere_username'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_username"
id="tw_anywhere_username" value="<?php
echo attribute_escape($tw_anywhere_username); ?>" /><br />
without "@"
</td>
</tr>

<tr>
<th><label for="tw_anywhere_commenttitle"><?php
_e('your twitter commenttitle', 'tw_anywhere_commenttitle'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_commenttitle"
id="tw_anywhere_commenttitle" value="<?php
echo attribute_escape($tw_anywhere_commenttitle); ?>" /><br />
Comment are header title.
</td>
</tr>

<tr>
<th><label for="tw_anywhere_commenttext"><?php
_e('your twitter commenttext', 'tw_anywhere_commenttext'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_commenttext"
id="tw_anywhere_commenttext" value="<?php
echo attribute_escape($tw_anywhere_commenttext); ?>" /><br />
Comment text.
</td>
</tr>

<tr>
<th><label for="tw_your_profile_show"><?php
_e('twitter your profile show?', 'tw_your_profile_show'); ?></label></th> <td><input type="checkbox" name="tw_your_profile_show"
id="tw_your_profile_show" value="1" <?php if ($tw_your_profile_show==1) {echo "checked ";} ?> />yes</td>
</tr>

<tr>
<th><label for="tw_anywhere_bitlyusername"><?php
_e('bit.ly username', 'tw_anywhere_bitlyusername'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_bitlyusername"
id="tw_anywhere_bitlyusername" value="<?php
echo attribute_escape($tw_anywhere_bitlyusername); ?>" /><br />
*If you use bit.ly API,put on.
</td>
</tr>

<tr>
<th><label for="tw_anywhere_bitlyapikey"><?php
_e('your bit.ly API key', 'tw_anywhere_bitlyapikey'); ?></label></th> <td><input size="36" type="text" name="tw_anywhere_bitlyapikey"
id="tw_anywhere_bitlyapikey" value="<?php
echo attribute_escape($tw_anywhere_bitlyapikey); ?>" /><br />
*If you use bit.ly API,put on.
</td>
</tr>

<tr>
<th><label for="tw_anyware_freearea"><?php
_e('好きな場所に設置?', 'tw_anyware_freearea'); ?></label></th> <td><input type="checkbox" name="tw_anyware_freearea"
id="tw_anyware_freearea" value="1" <?php if ($tw_anyware_freearea==1) {echo "checked ";} ?> />yes</td>
</tr>

</tbody></table>

<p class="submit">
<input type="submit" name="update_option" class="button- primary" value="<?php _e('Save Changes'); ?>" />
</p>

</form>
</div>

<?php
}


// プラグイン停止時にフィールドを削除
function remove_tw_anywhere()
{
	delete_option('tw_anywhere_api_key');
	delete_option('tw_anywhere_username');
	delete_option('tw_anywhere_commenttitle');
	delete_option('tw_anywhere_commenttext');
	delete_option('tw_your_profile_show');
	delete_option('tw_anywhere_bitlyusername');
	delete_option('tw_anywhere_bitlyapikey');
}


// WordPressプラグインとして登録するもの（ショートコードなど）
add_action('wp_head','add_anywhere_script');
add_action('wp_footer','add_footer_script');

// もしデフォルト値であれば、コンテンツ下に表示。そうでなければ、このフィルターは使用しない（好きな場所に設置）

$tw_anywhere_freearea= get_option('tw_anyware_freearea');
if ($tw_anywhere_freearea==0) {
add_filter('the_content', 'add_anyware_area');
}

// 管理画面、管理用
add_action('admin_menu', 'tw_anywhere_menu');
add_action('deactivate_tw-anywhere/tw-anywhere.php', 'remove_tw_anywhere');


?>