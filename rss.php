<?php
if(isset($_REQUEST["shop"]) && $_REQUEST["shop"]!="" && is_numeric($_REQUEST["shop"]))
{
    $shop_id=$_REQUEST["shop"];

    //Получаем список товаров
    $z=file_get_contents("https://seller.pokupo.ru/api/goods/".$shop_id."/search/");
    $r=json_decode($z,$assoc=true);

    //Получаем свойства магазина
    $s=file_get_contents("https://seller.pokupo.ru/api/shop/info/".$shop_id."/");
    $shop=json_decode($s,$assoc=true);


    header('Content-type: text/xml;charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8" ?>'; 

?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">


<channel>
    <title><?php echo $shop["name_shop"];?></title>
    <link>http://shop.brehen-sobaken.ru/rss.php?shop=<?php echo $shop_id; ?></link>
    <atom:link href="http://shop.brehen-sobaken.ru/rss.php?shop=<?php echo $shop_id; ?>"  rel="self" type="application/rss+xml" />    


    <?php
    echo "<description>".$shop["desc_shop"]."</description>";
    ?>

    <language>ru</language>
    <?php
    
    foreach($r as $key=>$val)
    {
 	if(isset($val["id"]))
	{


 
    ?>
	<item>
	<title><![CDATA[<?php echo $val['chort_name']; ?>]]></title>
	<guid><?php echo $shop["site_shop"]."/#/goods/id=".$val['id']; ?></guid>
	<link><?php echo $shop["site_shop"]."/#/goods/id=".$val['id']; ?></link>
	<description><![CDATA[<?php echo "<p><img src='http:".$val['route_image']."'></p>".$val["description"]."<p>Цена: ".$val['sell_cost']." руб.</p>"; ?>]]></description>
	<pubDate><?php echo date('r', strtotime($val["date_create"])); ?></pubDate>
	</item>    
    <?php
	}
    }
} 
?>
</channel>
</rss>