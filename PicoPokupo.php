<?php

/**
 * PokupoEmbedPlugin - Embed goods from pokupo.ru shop to your Pico page. Based on PicoEmbed plugin.
 * @author  astrizak
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
final class PicoPokupo extends AbstractPicoPlugin
{
    /**
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = true;
    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();
    /**
     * Triggered before Pico renders the page
     * @see    Pico::getTwig()
     * @see    Embed::onPageRendered()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
    {
        // Search for all [shop_id] shortcodes over the content
        preg_match_all( '#\[pkp_shop_[0-9]*?\]#s', $twigVariables['content'], $shop_matches );

        preg_match_all( '#\[pkp_good_[0-9]*?\]#s', $twigVariables['content'], $good_matches );

        $style="<style type='text/css'>
                .catitem {position:relative;width:250px; float:left; font-size:12px; height:200px; margin:0px; padding:20px; }
                .catitem:hover {background-color:#f6f6f6;}
                .catitem a {color:black; cursor:pointer;}
                .catitem p {background-color:transparent!important;}
                .catgroup{width:100%; padding-top:0px; padding-bottom:0px; min-height:0px;}
                #catalog{margin:0 auto; min-width:1000px; max-width:1000px; padding-bottom:80px;}
                </style>";

        // Make sure we found some shortcodes
        if(count($shop_matches[0])>0 || count($good_matches[0])>0)
        {
            // Get page content
            $new_content = &$twigVariables['content'];

            foreach($good_matches[0] as $match)
            {
                $good_id=preg_replace('/[a-z\_\[\]]*/',"",$match);

                $z=file_get_contents("https://seller.pokupo.ru/api/goods/".$good_id."/info/101000000/");
                $r=json_decode($z,$assoc=true);

                $goods_list=$this->getData($r);
                $new_content = preg_replace('/\[pkp_good_[0-9]*?\]/', $goods_list, $new_content,1);
            }


            // Walk through shortcodes one by one
            foreach($shop_matches[0] as $match)
            {
                $shop_id=preg_replace('/[a-z\_\[\]]*/',"",$match);

                $z=file_get_contents("https://seller.pokupo.ru/api/goods/".$shop_id."/search/?_format=json");
                $r=json_decode($z,$assoc=true);

                $shop_list=$this->getData($r);
                $new_content = preg_replace('/\[pkp_shop.*?\]/', $shop_list, $new_content,1);
            }

            $new_content = "<div id='catalog' >". $new_content ."</div>".$style;
        }

    }

    public function getData($r)
    {
        arsort($r);
        $i=0;
        foreach($r as $key=>$value)
        {
            if(isset($value["chort_name"]))
            {
                if($i==0)
                {
                    $goods_list.= '<div class="catgroup">';
                }

                $goods_list.='

	        <div class="catitem" title="'.strip_tags($value["description"]).'">
	            <a href="'.$value['route_big_image'].'" class="pop" >
	            <img src="'.$value['route_image'].'" height="120" alt="'.$value["full_name"].'" /></a><br/>
	            <strong>'.$value["chort_name"].'</strong>
                    <div id="click">Цена:'.$value["sell_cost"].'руб.&nbsp;&nbsp;
                        <a href="https://pokupo.ru/payment/'.$value["id_shop"].'/payment#//idGoods='.$value["id"].'&count=1"><strong style="font-size:14px; color:red;">Купить</strong></a>
                    </div>
	        </div>';

                $i++;
    
                if($i>2)
                {
                    $goods_list.= "</div>";
	            $i=0;
                }
            }
        }

        return $goods_list;
    }

}
