<?php
//Для корректной работы необходимо в ЛК продавца Покупо, в настройках магазина установить URL для уведомлений
//соответствующий адресу данного скрипта, а также пароль уведомлений, такой же какой задали в параметрах ниже

define('ID_SHOP','31065'); //Идентификатор магазина для CMS
define('notificationPassword', '123456'); //Пароль уведомлений из ЛК Покупо
define('key','123456'); //Ключ шифрования для ссылки доступа
define('interval','1'); //Количество дней в течение которого действуют ссылки


if(isset($_REQUEST['success']))
{
    echo "YES";
}


//Если параметр h для ссылки задан, расшифровываем его
//и проверяем дату и статус платежа
else if(isset($_REQUEST['h']) && $_REQUEST['h']!="")
{
    $c = base64_url_decode($_REQUEST['h']);
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, key, $options=OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, key, $as_binary=true);
    if (hash_equals($hmac, $calcmac))
    {
        //В данноми примере в ссылке используются 2 параметра разделенные знаком +
        $res=explode("+",$original_plaintext);

        $dt1 = new DateTime;
        $dt2 = new DateTime($res[1]);

        //Сравниваем теукущую дату и дату сохраненную в ссылке
        $interval = date_diff($dt1,$dt2);

        //Для дней ставим %a, для часов %h, для минут %i
        $intr=$interval->format('%i');

        //Если интервал больше чем задано, сообщаем о просроченной ссылке
        if($intr>interval)
        {
            echo "Ссылка просрочена.";
            getPaymentLink();
        }
        else
        {
            $ID_INVOICE = $res[0]; //Идентификатор заказа
            $hash = md5(ID_SHOP.$ID_INVOICE.notificationPassword);

            $pkp_url = 'https://seller4.dev7.pokupo.ru/api/payment/interfaces/get_status_pay?ID_SHOP='.ID_SHOP.'&ID_INVOICE='.$ID_INVOICE.'&HASH='.$hash;

            $content = file_get_contents($pkp_url);
            $data = json_decode($content,1);

            //дополнительно можно добавить проверку оплаченой суммы SUM_ORDER
            //https://dashboard.pokupo.ru/#/developers/payment_interfaces
            if($data["STATUS_PAYMENT"]=="paid" && $data["SUM_ORDER"]==300)
            {
                //Тут можно просто добавить шаблон со ссылками на видео
                echo "Вот ваш видеоархив";
            }
            else{
                echo "Доступ к видеоархиву не оплачен или ссылка устарела";
            }
        }
    }
    else
    {
        echo "Доступ к видеоархиву не оплачен или ссылка устарела";
    }
}


//Если параметр не найден, предлагаем пользователю оплатить заказ
else
{
    getPaymentLink();
}


function getPaymentLink()
{
    //В примере в качестве номера заказа используется timestamp, но можно поставить любой другой
    $ID_INVOICE=time();

    //сохраняем текущую дату в генерируемой ссылке для последующей проверки
    $dt = new DateTime;
    $link_date = $dt->format("Y-m-d G:i:s");


    //Сохраняем в ссылке номер заказа и его дату. Можно дополнительно хранить любые параметры
    //например сумму заказа, чтобы в зависимости от оплаты устанавливать различный срок действия ссылки и.т.д
    $plaintext = $ID_INVOICE."+".$link_date;

    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, key, $as_binary=true);
    $link = base64_url_encode( $iv.$hmac.$ciphertext_raw );

    echo 'Оплатите доступ к архиву<br><br>

    <form method="POST" action=" https://seller.pokupo.ru/api/ru/payment/merchant">
        <input type="hidden" name="LMI_PAYEE_PURSE" value="'.ID_SHOP.'">
        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="1.00">
        <input type="hidden" name="LMI_PAYMENT_DESC" value="Доступ к видеоархиву">
        <input type="hidden" name="LMI_PAYMENT_NO" value="'.$ID_INVOICE.'">
        <input type="hidden" name="PKP_SUCCESS_URL" value="http://testsite/arc.php?h='.$link.'">
        <input type="submit" value="Оплатить">
    </form>';
}


function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function base64_url_decode($input) {
 return base64_decode(strtr($input, '._-', '+/='));
}

?>