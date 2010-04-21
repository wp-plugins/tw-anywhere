<?php
/* これは文字化け防止のための日本語文字列です。
   このソースファイルは UTF-8 で保存されています。
   Above is a Japanese strings to avoid charset mis-understanding.
   This source file is saved with UTF-8. */
/*
Plugin Name: Tw Anyware
Plugin URI: http://vcsearch.web-service-api.jp/
Description: Add Twitter anyware API,connect and tweet,follow.
Author: wackey
Version: 1.01
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

//
function add_anyware_area($content) {
$content .='<p>
<div id="twitterConnectButton"></div>
<div id="twitterSignOut"></div><br />
<div id="twitterFollowButton"></div>
<div id="twitterUserInfo"></div>
<div id="twitterTweetBox"></div>
</p>';
return $content;
}

// フッターにjavascriptでいろいろ
function add_footer_script() {
$tw_anywhere_username= get_option('tw_anywhere_username');
?>
<script type="text/javascript">
// thx.yager http://creazy.net/2010/04/twitter_anywhere.html
var permalink = '<?php the_permalink() ?>';

/**
 * コネクト済みの場合の処理
 */
function anywhereConnected(twitter) {
    // コネクト済の場合はユーザー情報が取得できる
    // データの呼び出しは、user.data('データ名')
    var user = twitter.currentUser;

    // コネクトユーザー情報の表示
    document.getElementById('twitterUserInfo').innerHTML
        = '<img src="'+user.data('profile_image_url')+'" alt="'+user.data('screen_name')+'" /><br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'" target="_blank">'+user.data('name')+'</a><br />'
        + '<b>現在地</b> '+user.data('location')+'<br />'
        + '<b>Web</b> <a href="'+user.data('url')+'" target="_blank">'+user.data('url')+'</a><br />'
        + '<b>自己紹介</b> '+user.data('description')+'<br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'/following" target="_blank">'+user.data('friends_count')+'</a>following<br />'
        + '<a href="http://twitter.com/'+user.data('screen_name')+'/followers" target="_blank">'+user.data('followers_count')+'</a>followers<br />';

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
        label: "ツイッターでコメントが書けます",
        defaultContent: "@<?php echo stripslashes($tw_anywhere_username); ?> ここにコメントをお願いします " + permalink
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
//$this->upate_options(); ?>
<div class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p>
</div> <?php }
$tw_anywhere_api_key= get_option('tw_anywhere_api_key');
$tw_anywhere_username= get_option('tw_anywhere_username');
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
}


// WordPressプラグインとして登録するもの（ショートコードなど）
add_action('wp_head','add_anywhere_script');
add_action('wp_footer','add_footer_script');
add_filter('the_content', 'add_anyware_area');

// 管理画面、管理用
add_action('admin_menu', 'tw_anywhere_menu');
add_action('deactivate_tw-anywhere/tw-anywhere.php', 'remove_tw_anywhere');


?>