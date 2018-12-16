<?php

//Документация по api доступна по этому адресу
//https://seller.pokupo.ru/doc/api/v1/platform

if(isset($_REQUEST["shop"]) && $_REQUEST["shop"]!="" && is_numeric($_REQUEST["shop"]))
{
    $shop_id=$_REQUEST["shop"];

    //Получаем список товаров
    $z=file_get_contents("https://seller.pokupo.ru/api/goods/".$shop_id."/search/");
    $r=json_decode($z,$assoc=true);


    //Случайным образом выбираем один товар
    $i=rand(0, count($r["goods_list"])-1);

    //Получаем свойства магазина
    $s=file_get_contents("https://seller.pokupo.ru/api/shop/info/".$shop_id."/");
    $shop=json_decode($s,$assoc=true);
    
    echo "<div style='width:250px; text-align:center;'><a href='".$shop['site_shop']."/#/goods/id=".$r["goods_list"][$i]["id"]."' target='_blank'><br/>";
	
    //Краткое наименование
    echo $r[$i]["chort_name"]."<br/><br/>";

    //Превью фото
    echo "<img src='".$r["goods_list"][$i]["route_image"]."' width='100' /></a><br/><br/>";

    //Стоимость
    echo "Цена: ". $r["goods_list"][$i]["sell_cost"]." руб.<br/><br/>";

    echo "<a href='https://pokupo.ru/payment/".$shop_id."/payment#//idGoods=".$r["goods_list"][$i]["id"]."&count=1' target='_blank'><button>Купить</button></a></div>";


}

?>

