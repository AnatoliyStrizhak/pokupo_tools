<?php    
$g=file_get_contents("https://seller.pokupo.ru/api/goods/8591/search/?type_goods_info=yes");
$s=file_get_contents("https://seller.pokupo.ru/api/shop/shipping/list/8591/");

$rg=json_decode($g,$assoc=true);
$rs=json_decode($s,$assoc=true);

header("Content-Type: text/xml");

echo '<Ads formatVersion="3" target="Avito.ru">';


foreach($rg as $key=>$val)
{

    if($val["type_goods"]["name"]=="самовывоз" && $val["type_goods"]['parameters'][1]['name']=="avito" && $val["type_goods"]['parameters'][1]['value']==1)
    {

        echo '
        <Ad>
            <Id>'.$val['id'].'</Id>
            <AdStatus>Free</AdStatus>
            <AllowEmail>Нет</AllowEmail>';

        foreach($rs['methods_shipping'] as $k=>$v)
        {
            if($v['name_method_shipping']=="Самовывоз")
            {
                echo '<Region>'.$v['shipping_pickup_list'][0]['name_region'].'</Region>
                <City>'.$v['shipping_pickup_list'][0]['name_city'].'</City>
                <ContactPhone>'.$v['shipping_pickup_list'][0]['contact_phone'].'</ContactPhone>';
            }
        }

        echo '    
            <Category>'.$val["type_goods"]['parameters'][2]['value'].'</Category>
            <GoodsType>'.$val["type_goods"]['parameters'][3]['value'].'</GoodsType>
            <Title>'.$val['chort_name'].'</Title>
            <Description>'.strip_tags($val['description']).'</Description>
            <Price>'.$val['sell_cost'].'</Price>
            <Images>
                <Image url="https:'.$val["route_big_image"].'" />';

        $gal=file_get_contents("https://seller.pokupo.ru/api/goods/".$val['id']."/info/11000000/");
        $img=json_decode($gal,$assoc=true);

        foreach($img['gallery'] as $k=>$v)
        {
            $p=preg_replace("/gallery/","big/gallery", $v['route_photo']);
            echo '<Image url="https:'.$p.'" />';
        }
            echo '</Images>
        </Ad>';
    }
}

echo '</Ads>';

?>
