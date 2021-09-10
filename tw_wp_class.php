<?php
//ini_set("display_errors",1);
require_once(__DIR__ . '/../wp-load.php');
require_once("../vendor/autoload.php");
require_once("../tw/tw-config.php");

use Abraham\TwitterOAuth\TwitterOAuth;

define("BLOGPOSTKEYWORD", "#BLOGPOST");


class tw_wp_class
{
    public static function main()
    {
        $txt = (function () {
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
            $res = $connection->get('statuses/user_timeline', array(
                'screen_name'       => 'taoka358s',
                'count'             => '5',
                'exclude_replies'   => 'true',
                'include_rts'       => 'false'
            ));
            return $res;
        })();
        return (new class($txt)
        {
            public $txt = "";
            public function __construct($txt)
            {
                $this->txt = $txt;
            }
            public function wp_post()
            {
                $wp_error = $timeH = $wp_post_txt = null;

                foreach ($this->txt as $key => $val) {
                    if (preg_match("/" . BLOGPOSTKEYWORD . "/", $val->text)) {
                        $wp_post_txt[] = $val->text;
                        $timeH[] = wp_date('H', (strtotime($val->created_at)));
                    }
                }

                if (count($timeH)) {
                    foreach ($timeH as $key => $val) {
                        if ($wp_post_txt[$key] !== "" && wp_date("H") === $timeH[$key]) {
                            $my_post = array(
                                'post_title' => 'Twitterからの投稿 - ' . wp_date('Y-m-d H:i:s'),
                                'post_content' => $wp_post_txt[$key],
                                'post_status' => 'publish',
                                'post_author' => 1,
                                'post_category' => array(1)
                            );
                            $post_id = wp_insert_post($my_post, $wp_error);
                        }
                    }
                }
            }
        });
    }
}
if ($argv[0]) {
    tw_wp_class::main()->wp_post();
}
