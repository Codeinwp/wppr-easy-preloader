<?php
/*
Plugin Name: WPPR Easy Preloader
Description: Using the preloader you can easily import settings from other posts, saving a lot of time in the long-term
Version: 1.1.1
Author: Themeisle
Author URI:  https://themeisle.com/
Plugin URI: https://themeisle.com/plugins/wppr-easy-preloader/
Requires at least: 3.5
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cwppos
Domain Path: /languages
*/

define("WPPR_PRELOADER_VERSION","1.1.1");
define("WPPR_PRELOADER_PATH", realpath(dirname(__FILE__)));

function wppr_ep_js_preloader() {

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            function wppr_ep_cwpPreloadOptions(item) {
                for (var i = 1; i <= <?php echo cwppos("cwppos_option_nr");?>; i++) {
                    var preloadListItem = jQuery(item).parent().parent().children(".cwp_pitem_info").children(".cwp_pitem_options_content").children("li:eq("+(i-1)+")").text();
                    if(preloadListItem != "-") { jQuery("input#option_" + i + "_content").val(preloadListItem); }
                };
            }

            function wppr_ep_cwpPreloadCons(item)
            {
                for (var i = 1; i <= <?php echo cwppos("cwppos_option_nr");?>; i++) {
                    var preloadListItem = jQuery(item).parent().parent().children(".cwp_pitem_info").children(".cwp_pitem_options_pros").children("li:eq("+(i-1)+")").text();
                    if(preloadListItem != "-") { jQuery("input#cwp_option_" + i + "_pro").val(preloadListItem); }
                };
            }

            function wppr_ep_cwpPreloadPros(item)
            {
                for (var i = 1; i <= <?php echo cwppos("cwppos_option_nr");?>; i++) {
                    var preloadListItem = jQuery(item).parent().parent().children(".cwp_pitem_info").children(".cwp_pitem_options_cons").children("li:eq("+(i-1)+")").text();
                    if(preloadListItem != "-") { jQuery("input#cwp_option_" + i + "_cons").val(preloadListItem); }
                };
            }

            jQuery(".preload_info").click(function(e){
                e.preventDefault();

                var cwpThemeUrl = '<?php echo plugins_url( '', __FILE__ ); ?>';
                var ajaxLoad = "<img class='ajax_load_icon' src='" + cwpThemeUrl +"/images/ajaxload.gif' alt='Loading...'/>";

                jQuery("body #wpwrap").append("<div class='preload_result'><div class='preload_inner'><header><h2>Preload Info</h2><div class='preload_close'></div></header><div class='preloader_body'><ul class='preload_list'></ul></div></div></div>");
                jQuery(".preload_result").fadeIn();

                jQuery(".preload_close").bind("click", function(){
                    jQuery(".preload_result").fadeOut();
                });
                jQuery(".preload_list").html(ajaxLoad);

                jQuery.get(ajaxurl,{'action':'cwp_load_preloader'},
                    function(response){
                        jQuery(".preload_list").html(response);
                    })

                //jQuery(".preload_list").html(ajaxLoad).load(loadUrl);


                jQuery(".preload_list .cwp_p_title").live("click", function(){
                    jQuery(this).parent().parent().children(".cwp_pitem_info").slideToggle();
                });

                jQuery(".preload_list li button.preload").live("click", function(){
                    wppr_ep_cwpPreloadOptions(this);
                    wppr_ep_cwpPreloadCons(this);
                    wppr_ep_cwpPreloadPros(this);
                    jQuery(".preload_result").fadeOut();
                });

                jQuery(".preload_list .cwp_pitem_options_content li").live("click",function(){
                    var plIndex = jQuery(this).index();
                    var preloadListItem = jQuery(this).text();
                    if(preloadListItem != "-") { jQuery("input#option_" + plIndex + "_content").val(preloadListItem); }
                });

                jQuery(".preload_list .cwp_pitem_options_pros li").live("click",function(){
                    var plIndex = jQuery(this).index();
                    var preloadListItem = jQuery(this).text();
                    if(preloadListItem != "-") { jQuery("input#cwp_option_" + plIndex + "_pro").val(preloadListItem); }
                });

                jQuery(".preload_list .cwp_pitem_options_cons li").live("click",function(){
                    var plIndex = jQuery(this).index();
                    var preloadListItem = jQuery(this).text();
                    if(preloadListItem != "-") { jQuery("input#cwp_option_" + plIndex + "_cons").val(preloadListItem); }
                });

            });
        });
    </script>
    <?php
}

function wppr_ep_backend_preloader(){
    global $post;
    $args = array(
        'offset'           => 0,
        'post_type'        => array('any'),
        'post__not_in' => array($post->ID),
        'meta_query'             => array(
            array(
                'key'       => 'cwp_meta_box_check',
                'value'     => 'Yes',
            ),
        ),
    );

    $cwp_query = new WP_Query($args);
    //var_dump($cwp_query);
    while ($cwp_query->have_posts()) : $cwp_query->the_post();
        //var_dump($post);
        $post_id = $post->ID;
        $preloaded_info = array();
        $preloaded_info[$post_id] = array();

        ?>
        <li class="cwp_preloaded_item cwpr_clearfix">
            <header>

                <h3 class="cwp_p_title"><?php the_title(); ?></h3>
                <button class="preload" title="Preload all details">&curarr;</button>
            </header>
            <?php

            for ($i=1; $i <=cwppos("cwppos_option_nr"); $i++) {
                $preloaded_info[$post_id]["option".$i] = array(
                    "content" => get_post_meta($post->ID, "option_" . $i ."_content", true),
                    "grade" => get_post_meta($post->ID, "option_" . $i ."_grade", true),
                    "pro"   => get_post_meta($post->ID, "cwp_option_". $i ."_pro", true),
                    "cons"  => get_post_meta($post->ID, "cwp_option_". $i ."_cons", true),
                );
            }
            //var_dump($preloaded_info);
            ?>

            <div class="cwp_pitem_info post_<?php echo $post_id; ?>">
                <ul class="cwp_pitem_options_content">
                    <h4><?php _e("Options", "cwppos"); ?></h4>
                    <?php
                    for ($i=1; $i <= cwppos("cwppos_option_nr"); $i++) {
                        $pinfo_temp = $preloaded_info[$post_id]["option". $i]['content'];
                        if (!empty($pinfo_temp)) {
                            echo "<li>" . $pinfo_temp. "</li>";
                        } else {
                            echo "<li>-</li>";
                        }
                    }
                    ?>
                </ul><!-- end .cwp_pitem_options_content -->

                <ul class="cwp_pitem_options_pros">
                    <h4><?php _e("Pros", "cwppos"); ?></h4>
                    <?php
                    for ($i=1; $i <=cwppos("cwppos_option_nr"); $i++) {
                        $pinfo_temp = $preloaded_info[$post_id]["option". $i]['pro'];
                        if (!empty($pinfo_temp)) {
                            echo "<li>" . $pinfo_temp. "</li>";
                        } else {
                            echo "<li>-</li>";
                        }
                    }
                    ?>
                </ul><!-- end .cwp_pitem_options_pros -->

                <ul class="cwp_pitem_options_cons">
                    <h4><?php _e("Cons", "cwppos"); ?></h4>
                    <?php
                    for ($i=1; $i <=cwppos("cwppos_option_nr"); $i++) {
                        $pinfo_temp = $preloaded_info[$post_id]["option". $i]['cons'];
                        if (!empty($pinfo_temp)) {
                            echo "<li>" . $pinfo_temp. "</li>";
                        } else {
                            echo "<li>-</li>";
                        }
                    }
                    ?>
                </ul><!-- end .cwp_pitem_options_cons -->
            </div><!-- end .cwp_pitem_info -->
        </li><!-- end .cwp_preloaded_item -->
    <?php endwhile; wp_reset_postdata();

    die(); // this is required to terminate immediately and return a proper response
}

// Added by Ash/Upwork
function wppr_ep_load_dependencies(){
    require_once WPPR_PRELOADER_PATH . "/lib/dependencies/tgm-activation.php";
}
add_action('plugins_loaded', 'wppr_ep_load_dependencies');
// Added by Ash/Upwork

