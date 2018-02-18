<?php
//Генерируем таблицу с товарами pokupo в markdown
//Документация по api доступна по этому адресу
//https://seller.pokupo.ru/doc/api/v1/platform

if(isset($_REQUEST["shop"]) && $_REQUEST["shop"]!="" && is_numeric($_REQUEST["shop"]))
{
    $shop_id=$_REQUEST["shop"];
 
    if(isset($_REQUEST["col"]) && $_REQUEST["col"]!="" && is_numeric($_REQUEST["col"]))
    {
	$col=$_REQUEST["col"];
    }
    else
    {
        $col=4;
    }
 
    header("Content-Type: text/plain");


    //Получаем список товаров
    $z=file_get_contents("https://seller.pokupo.ru/api/goods/".$shop_id."/search/");
    $r=json_decode($z,$assoc=true);
    
    arsort($r);
    

    $i=0;

    //Если указан параметр mark, выводим результат в формате markdown
    if(isset($_REQUEST["mark"]) && $_REQUEST["mark"]=="true")
    {
    
	$td1="";
	$td2="";
	$td3="";
	$td4="";

    
	foreach($r as $key=>$val)
	{
	    //Получаем информацию по товару, в качестве последнего параметра infoType указываем 10100000 
	    //- основная информация по товару и раздел shop_info
	    $goods=file_get_contents("https://seller.pokupo.ru/api/goods/".$val['id']."/info/101000000/");
	    $info=json_decode($goods,$assoc=true);
   
	    if(isset($val["id"]))
	    {
		$td1.= "|[".$val["chort_name"]."](".$info['shop']['site_shop']."#/goods/id=".$val["id"].")";
		$td2.= "| --------  ";
        	$td3.= "|![](https://imgp.golos.io/100x60/https:".$info["main"]["main_photo"]["route_photo"].")";
		$td4.= "| [Купить](https://pokupo.ru/payment/".$shop_id."/payment#//idGoods=".$val['id']."&count=1) за ". $info["main"]["sell_end_cost"] ." руб.";
	    }
		
	    $i++;
    
	    if($i>($col-1))
	    {
		$td1.="|\n";
		$td2.="|\n";
		$td3.="|\n";
		$td4.="|\n";
      
		echo $td1.$td2.$td3.$td4;  
      
    		echo "\n\n";
    	    
    		$i=0;

		$td1="";
		$td2="";
		$td3="";
		$td4="";
	    }
	}
    
    }
    else
    {
	$td1="<tr>";
	$td2="<tr>";
	$td3="<tr>";
    
	echo "<table>";
    
	foreach($r as $key=>$val)
	{
	    //Получаем информацию по товару, в качестве последнего параметра infoType указываем 10100000 
	    //- основная информация по товару и раздел shop_info
	    $goods=file_get_contents("https://seller.pokupo.ru/api/goods/".$val['id']."/info/101000000/");
	    $info=json_decode($goods,$assoc=true);
   
	    if(isset($val["id"]))
	    {
		$td1.= "<td><center><a href='".$info['shop']['site_shop']."#/goods/id=".$val['id']."'>".$val['chort_name']."</a></center></td>";
        	$td2.= "<td><center><img src='https://imgp.golos.io/100x60/https:".$info['main']['main_photo']['route_photo']."'></center></td>";
		$td3.= "<td><center>". $info['main']['sell_end_cost'] ." руб.  <br/><br/><a href='https://pokupo.ru/payment/".$shop_id."/payment#//idGoods=".$val['id']."&count=1'><button>Купить</button></a></center></td>";
	    }
		
	    $i++;
    
	    if($i>($col-1))
	    {
		$td1.="</tr>";
		$td2.="</tr>";
		$td3.="</tr>";
	          
		echo $td1.$td2.$td3;  
      
    		echo "\n\n";
    	    
    		$i=0;

		$td1="<tr>";
		$td2="<tr>";
		$td3="<tr>";
	    
	    }
	}

	echo "</table>";
    }
}
else
{
    echo "<center><form method='get' action=''><input type='text' value='' name='shop'><input type='submit' value='OK'></form></center>";

}

?>

