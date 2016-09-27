<?php 
/*
 * @version     $Id: helper.php 095 2013-01-25 17:25:00Z Anton Wintergerst $
 * @package     JiSocialWidgets Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgJiSocialWidgetsHelper {
    public static function &getHTML($params, $options) {
        $twitter_text = $params->get('twitter_text','Tweet');
        
        $classes = '';
        // Facebook Widget Classes
        if(isset($options['like']['enabled']) && $options['like']['enabled']=='show') $classes.= ' like';
        if(isset($options['subscribe']['enabled']) && $options['subscribe']['enabled']=='show') $classes.= ' subscribe';
        if(isset($options['comments']['enabled']) && $options['comments']['enabled']=='show') $classes.= ' comments';
        if(isset($options['page']['enabled']) && $options['page']['enabled']=='show') $classes.= ' page';
        // Twitter Widget Classes
        if(isset($options['tweet']['enabled']) && $options['tweet']['enabled']=='show') $classes.= ' tweet';
        if(isset($options['timeline']['enabled']) && $options['timeline']['enabled']=='show') $classes.= ' timeline';
        // Google Plus Widget Classes
        if(isset($options['plus1']['enabled']) && $options['plus1']['enabled']=='show') $classes.= ' plus1';
        if(isset($options['plus']['enabled']) && $options['plus']['enabled']=='show') $classes.= ' plus';
        // LinkedIn Widget Classes
        if(isset($options['inshare']['enabled']) && $options['inshare']['enabled']=='show') $classes.= ' inshare';
        if(isset($options['inmember']['enabled']) && $options['inmember']['enabled']=='show') $classes.= ' inmember';
        // Pinterest Widget Classes
        if(isset($options['pinit']['enabled']) && $options['pinit']['enabled']=='show') $classes.= ' pinit';
        if(isset($options['pinterest']['enabled']) && $options['pinterest']['enabled']=='show') $classes.= ' pinterest';
        // Disqus Widget Classes
        if(isset($options['disqus']['enabled']) && $options['disqus']['enabled']=='show') $classes.= ' disqus';
        // Custom Classes
        if(isset($options['class'])) $classes.= $options['class'];
        
        // Start Creating Widgets
        $html = '<div class="jisocialwidgets'.$classes.'">';
        // Create Widgets
        foreach($options as $type=>$option) {
            if(isset($option['enabled']) && $option['enabled']=='show') {
                // Facebook Widgets
                if($type=='like') {
                    // Get Data Options
                    $settings = $options['like'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['like_href'])) $settings['like_href'] = $params->get('like_href', JURI::current());
                    if(!isset($settings['like_send'])) $settings['like_send'] = $params->get('like_send', 'true');
                    if(!isset($settings['like_layout'])) $settings['like_layout'] = $params->get('like_layout', 'button_count');
                    if(!isset($settings['like_width'])) $settings['like_width'] = $params->get('like_width', 270);
                    if(!isset($settings['like_show-faces'])) $settings['like_show-faces'] = $params->get('like_show-faces', 'true');
                    if(!isset($settings['like_action']) && $params->get('like_action')!=null) $settings['like_action'] = $params->get('like_action');
                    if(!isset($settings['like_colorscheme']) && $params->get('like_colorscheme')!=null) $settings['like_colorscheme'] = $params->get('like_colorscheme', 'light');
                    if(!isset($settings['like_font']) && $params->get('like_font')!=null) $settings['like_font'] = $params->get('like_font');
                    
                    if(!isset($settings['like_height'])) $settings['like_height'] = $params->get('like_height', 35);
                    if(!isset($settings['like_img']) && $params->get('like')) $settings['like_height'] = $params->get('like_height', 35);
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('like_', ' data-', $var).'="'.$setting.'"';
                    }
                    // Create Widget
                    $html.= '<div class="fb-like"'.$data.'> </div>';
                } elseif($type=='subscribe') {
                    // Get Data Options
                    $settings = $options['subscribe'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['subscribe_href'])) $settings['subscribe_href'] = $params->get('subscribe_href', 'https://www.facebook.com/zuck');
                    if(!isset($settings['subscribe_layout'])) $settings['subscribe_layout'] = $params->get('subscribe_layout', 'button_count');
                    if(!isset($settings['subscribe_width'])) $settings['subscribe_width'] = $params->get('subscribe_width', 270);
                    if(!isset($settings['subscribe_show-faces'])) $settings['subscribe_show-faces'] = $params->get('subscribe_show-faces', 'true');
                    if(!isset($settings['subscribe_colorscheme']) && $params->get('subscribe_colorscheme')!=null) $settings['subscribe_colorscheme'] = $params->get('subscribe_colorscheme', 'light');
                    if(!isset($settings['subscribe_font']) && $params->get('subscribe_font')!=null) $settings['subscribe_font'] = $params->get('subscribe_font');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('subscribe_', ' data-', $var).'="'.$setting.'"';
                    }
                    // Create Widget
                    $html.= '<div class="fb-subscribe"'.$data.'> </div>';
                } elseif($type=='comments') {
                    // Get Data Options
                    $settings = $options['comments'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['comments_href'])) $settings['comments_href'] = $params->get('comments_href', JURI::current());
                    if(!isset($settings['comments_num-posts'])) $settings['comments_num-posts'] = $params->get('comments_num-posts', 2);
                    if(!isset($settings['comments_width'])) $settings['comments_width'] = $params->get('comments_width', 460);
                    if(!isset($settings['comments_colorscheme']) && $params->get('comments_colorscheme')!=null) $settings['comments_colorscheme'] = $params->get('comments_colorscheme', 'light');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('comments_', ' data-', $var).'="'.$setting.'"';
                    }
                    // Create Widget
                    $html.= '<div class="fb-comments"'.$data.'> </div>';
                } elseif($type=='page') {
                    // Get Data Options
                    $settings = $options['page'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['page_href'])) $settings['page_href'] = $params->get('page_href', 'http://www.facebook.com/platform');
                    if(!isset($settings['page_width'])) $settings['page_width'] = $params->get('page_width', 460);
                    if(!isset($settings['page_height']) && $params->get('page_height')!=null) $settings['page_height'] = $params->get('page_height');
                    if(!isset($settings['page_colorscheme'])) $settings['page_colorscheme'] = $params->get('page_colorscheme', 'light');
                    if(!isset($settings['page_show-faces'])) $settings['page_show-faces'] = $params->get('page_show-faces', 'true');
                    if(!isset($settings['page_border-color']) && $params->get('page_border-color')!=null) $settings['page_border-color'] = $params->get('page_border-color');
                    if(!isset($settings['page_stream'])) $settings['page_stream'] = $params->get('page_stream', 'true');
                    if(!isset($settings['page_header'])) $settings['page_header'] = $params->get('page_header', 'true');
                    if(!isset($settings['page_force-wall'])) $settings['page_force-wall'] = $params->get('page_force-wall', 'false');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('page_', ' data-', $var).'="'.$setting.'"';
                    }
                    // Create Widget
                    $html.= '<div class="fb-like-box"'.$data.'> </div>';
                // Twitter Widgets
                } elseif($type=='tweet') {
                    // Get Data Options
                    $settings = $options['tweet'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['tweet_url']) && $params->get('tweet_url')!=null) $settings['tweet_url'] = $params->get('tweet_url', JURI::current());
                    if(!isset($settings['tweet_text']) && $params->get('tweet_text')!=null) $settings['tweet_text'] = $params->get('tweet_text');
                    if(!isset($settings['tweet_related']) && $params->get('tweet_related')!=null) $settings['tweet_related'] = $params->get('tweet_related');
                    if(!isset($settings['tweet_count']) && $params->get('tweet_count')!=null) $settings['tweet_count'] = $params->get('tweet_count');
                    if(!isset($settings['tweet_lang'])) $settings['tweet_lang'] = $params->get('tweet_lang', 'en');
                    if(!isset($settings['tweet_counturl']) && $params->get('tweet_counturl')!=null) $settings['tweet_counturl'] = $params->get('tweet_counturl', JURI::current());
                    if(!isset($settings['tweet_hashtags']) && $params->get('tweet_hashtags')!=null) $settings['tweet_hashtags'] = $params->get('tweet_hashtags');
                    if(!isset($settings['tweet_size']) && $params->get('tweet_size')!=null) $settings['tweet_size'] = $params->get('tweet_size');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('tweet_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="tweet"><a href="https://twitter.com/share" class="twitter-share-button"'.$data.'>'.$twitter_text.'</a></div>';
                } elseif($type=='timeline') {
                    // Get Data Options
                    $settings = $options['timeline'];
                    unset($settings['enabled']);
                    unset($settings['timeline_width']);
                    unset($settings['timeline_height']);
                    unset($settings['timeline_href']);
                    unset($settings['timeline_text']);
                    
                    // Set Global/Default Options
                    $width = (isset($options['timeline']['timeline_width']))? $options['timeline']['timeline_width']:$params->get('timeline_width', '460');
                    $height = (isset($options['timeline']['timeline_height']))? $options['timeline']['timeline_height']:$params->get('timeline_height', '500');
                    $href = (isset($options['timeline']['timeline_href']))? $options['timeline']['timeline_href']:$params->get('timeline_href', 'https://twitter.com/twitterapi');
                    $text = (isset($options['timeline']['timeline_text']))? $options['timeline']['timeline_text']:$params->get('timeline_text', 'Tweets');
                    
                    if(!isset($settings['timeline_widget-id'])) $settings['timeline_widget-id'] = $params->get('timeline_widget-id', '0');
                    if(!isset($settings['timeline_theme']) && $params->get('timeline_theme')!=null) $settings['timeline_theme'] = $params->get('timeline_theme');
                    if(!isset($settings['timeline_link-color']) && $params->get('timeline_link-color')!=null) $settings['timeline_link-color'] = $params->get('timeline_link-color');
                    if(!isset($settings['timeline_lang']) && $params->get('timeline_lang')!=null) $settings['timeline_lang'] = $params->get('timeline_lang', 'en');
                    if(!isset($settings['timeline_related']) && $params->get('timeline_related')!=null) $settings['timeline_related'] = $params->get('timeline_related');
                    if(!isset($settings['timeline_aria-polite']) && $params->get('timeline_aria-polite')!=null) $settings['timeline_aria-polite'] = $params->get('timeline_aria-polite');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('timeline_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="timeline"><a class="twitter-timeline" width="'.$width.'" height="'.$height.'" href="'.$href.'"'.$data.'>'.$text.'</a></div>';
                // Google Plus Widgets
                } elseif($type=='plus1') {
                    // Get Data Options
                    $settings = $options['plus1'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['plus1_size'])) $settings['plus1_size'] = $params->get('plus1_size', 'medium');
                    if(!isset($settings['plus1_annotation'])) $settings['plus1_annotation'] = $params->get('plus1_annotation', 'bubble');
                    if(!isset($settings['plus1_width'])) $settings['plus1_width'] = $params->get('plus1_width', '300');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('plus1_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="plus1"><div class="g-plusone"'.$data.'> </div></div>';
                } elseif($type=='plus') {
                    // Get Data Options
                    $settings = $options['plus'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['plus_href'])) $settings['plus_href'] = $params->get('plus_href', 'https://plus.google.com/+LarryPage');
                    if(!isset($settings['plus_rel'])) $settings['plus_rel'] = $params->get('plus_rel', 'author');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('plus_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="g-plus"'.$data.'> </div>';
                // LinkedIn Widgets
                } elseif($type=='inshare') {
                    // Get Data Options
                    $settings = $options['inshare'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['inshare_counter'])) $settings['inshare_counter'] = $params->get('inshare_counter', 'right');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('inshare_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="inshare"><script type="IN/Share"'.$data.'> </script> </div>';
                } elseif($type=='inmember') {
                    // Get Data Options
                    $settings = $options['inmember'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['inmember_id'])) $settings['inmember_id'] = $params->get('inmember_id', 'http://www.linkedin.com/in/reidhoffman');
                    if(!isset($settings['inmember_format'])) $settings['inmember_format'] = $params->get('inmember_format', 'inline');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        $data.= str_replace('inmember_', ' data-', $var).'="'.$setting.'"';
                    }
                    $html.= '<div class="inmember"><script type="IN/MemberProfile"'.$data.'> </script> </div>';
                // Pinterest Widgets
                } elseif($type=='pinit') {
                    // Get Data Options
                    $settings = $options['pinit'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['pinit_href'])) $settings['pinit_href'] = $params->get('pinit_href', JURI::current());
                    if(!isset($settings['pinit_count-layout'])) $settings['pinit_count-layout'] = $params->get('pinit_count-layout', 'horizontal');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        if($var!='pinit_href') {
                            $data.= str_replace('pinit_', ' ', $var).'="'.$setting.'"';
                        }
                    }
                    $html.= '<div class="pinit"><a href="http://pinterest.com/pin/create/button/?url='.$settings['pinit_href'].'" class="pin-it-button"'.$data.'>Pin It</a></div>';
                } elseif($type=='pinterest') {
                    // Get Data Options
                    $settings = $options['pinterest'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['pinterest_href'])) $settings['pinterest_href'] = $params->get('pinterest_href', 'http://pinterest.com/ben/');
                    if(!isset($settings['pinterest_layout'])) $settings['pinterest_layout'] = $params->get('pinterest_layout', 'standard');
                    
                    $html.= '<div class="pinterest"><a href="'.$settings['pinterest_href'].'">';
                    if($settings['pinterest_layout']=='standard') {
                        $html.= '<img src="http://passets-lt.pinterest.com/images/about/buttons/follow-me-on-pinterest-button.png" width="169" height="28" alt="Follow Me on Pinterest" />';
                    } elseif($settings['pinterest_layout']=='small') {
                        $html.= '<img src="http://passets-lt.pinterest.com/images/about/buttons/pinterest-button.png" width="80" height="28" alt="Follow Me on Pinterest" />';
                    } elseif($settings['pinterest_layout']=='square') {
                        $html.= '<img src="http://passets-cdn.pinterest.com/images/about/buttons/big-p-button.png" width="60" height="60" alt="Follow Me on Pinterest" />';
                    } elseif($settings['pinterest_layout']=='tiny') {
                        $html.= '<img src="http://passets-lt.pinterest.com/images/about/buttons/small-p-button.png" width="16" height="16" alt="Follow Me on Pinterest" />';
                    }
                    $html.= '</a></div>';
                // Disqus Widgets
                } elseif($type=='disqus') {
                    // Get Data Options
                    $settings = $options['disqus'];
                    unset($settings['enabled']);
                    // Set Global/Default Options
                    if(!isset($settings['disqus_shortname'])) $settings['disqus_shortname'] = $params->get('disqus_shortname', 'disqus');
                    if(!isset($settings['disqus_title']) && $params->get('disqus_title')!=null) $settings['disqus_title'] = $params->get('disqus_title');
                    if(!isset($settings['disqus_developer']) && $params->get('disqus_developer')!=null) $settings['disqus_developer'] = $params->get('disqus_developer');
                    if(!isset($settings['disqus_identifier']) && $params->get('disqus_identifier')!=null) $settings['disqus_identifier'] = $params->get('disqus_url');
                    if(!isset($settings['disqus_url']) && $params->get('disqus_url')!=null) $settings['disqus_url'] = $params->get('disqus_url', JURI::current());
                    if(!isset($settings['disqus_category_id']) && $params->get('disqus_category_id')!=null) $settings['disqus_category_id'] = $params->get('disqus_category_id');
                    // Build Data Tags
                    $data = '';
                    foreach($settings as $var=>$setting) {
                        if($var!='disqus_href') {
                            $data.= 'var '.$var.'="'.$setting.'";';
                        }
                    }
                    $html.= '<div id="disqus_thread">&nbsp;</div>';
                    $html.= '<script type="text/javascript">'.$data.'</script>';
                    $html.= '<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>';
                    $html.= '<a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>';
                }
            }
        }
        // Finish Creating Widgets
        $html.= '</div>';
        
        return $html;
    }
}