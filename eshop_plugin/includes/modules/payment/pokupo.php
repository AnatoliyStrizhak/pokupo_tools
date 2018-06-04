<?php
/**
Платежный модуль POKUPO для CMS ECSHOP
v.1.0
https://pokupo.ru
**/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/pokupo.php';

if (file_exists($payment_lang))
{
    global $_LANG;
    include_once($payment_lang);
}


/* The basic modules of information */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* Code */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* Description of the corresponding language */
    $modules[$i]['desc']    = 'pokupo_desc';

    /* Whether to support */
    $modules[$i]['is_cod']  = '0';

    /* Whether to support the on-line payment */
    $modules[$i]['is_online']  = '1';

    /* Pay */
    $modules[$i]['pay_fee'] = '0';

    /* Author */
    $modules[$i]['author']  = 'pokupo.ru';

    /* Website */
    $modules[$i]['website'] = 'pokupo.ru';

    /* Version */
    $modules[$i]['version'] = '1.0.0';

    /* Configuration information */
    $modules[$i]['config'] = array(
        array('name' => 'pokupo_account', 'type' => 'text', 'value' => ''),
        array('name' => 'pokupo_key',     'type' => 'text', 'value' => ''),
    );

    return;
}


/**
 * Class
 */
class pokupo
{
    /**
     * Constructors
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function pokupo()
    {}

    function __construct()
    {
        $this->pokupo();
    }

    /**
     * Генерируем платежную форму
     * @param   array   $order      Order Information
     * @param   array   $payment    Payment Information
     */
    function get_code($order, $payment)
    {
        $data_account = trim($payment['pokupo_account']);
        $data_orderid= $order['order_id'];
        $data_amount= $order['order_amount'];
        $mch_name = $GLOBALS['_CFG']['shop_name']; 

        $successUrl=urlencode("http://".$_SERVER['SERVER_NAME']."/respond.php?code=pokupo&st=ok");
        $failUrl=urlencode("http://".$_SERVER['SERVER_NAME']."/respond.php?code=pokupo&st=fail");
        $backUrl="http://".$_SERVER['SERVER_NAME'];

        $def_url  = '<br /><form style="text-align:center;" method=post action="https://seller.pokupo.ru/api/ru/payment/merchant" target="_blank">';
        $def_url .= "<input type=HIDDEN name='LMI_PAYEE_PURSE' value='".$data_account."'>";
        $def_url .= "<input type=HIDDEN name='LMI_PAYMENT_NO' value='".$data_orderid."'>";
        $def_url .= "<input type=HIDDEN name='LMI_PAYMENT_AMOUNT' value='".$data_amount."'>";
        $def_url .= "<input type=HIDDEN name='LMI_PAYMENT_DESC' value='".$mch_name." PAY ID: ".$order['order_sn']."'>";

        $def_url .= "<input type=HIDDEN name='LMI_SUCCESS_URL' value='".$successUrl."'>";
        $def_url .= "<input type=HIDDEN name='LMI_FAIL_URL' value='".$failUrl."'>";
        $def_url .= "<input type=HIDDEN name='LMI_BACK_URL' value='".$backUrl."'>";
        $def_url .= "<input type=HIDDEN name='CLIENT_MAIL' value='".$order['email']."'>";

        $def_url .= "<input type=submit value='" .$GLOBALS['_LANG']['pay_button']. "'>";
        $def_url .= "</form>";

        return $def_url;
    }

    /**
    * Проверяем параметры, генерируем хэш и проводим платеж
    */
    function respond()
    {
        $payment = get_payment(basename(__FILE__, '.php'));

        $LMI_PAYMENT_NO = $_REQUEST['LMI_PAYMENT_NO'];
        $LMI_PAYEE_PURSE = $_REQUEST['LMI_PAYEE_PURSE'];
        $LMI_PAYMENT_AMOUNT = $_REQUEST['LMI_PAYMENT_AMOUNT'];
        $LMI_MODE = $_REQUEST['LMI_MODE'];
        $LMI_SYS_INVS_NO = $_REQUEST['LMI_SYS_INVS_NO'];
        $LMI_SYS_TRANS_NO = $_REQUEST['LMI_SYS_TRANS_NO'];
        $LMI_SYS_TRANS_DATE = $_REQUEST['LMI_SYS_TRANS_DATE'];
        $LMI_PAYER_PURSE = $_REQUEST['LMI_PAYER_PURSE'];
        $LMI_PAYER_WM = $_REQUEST['LMI_PAYER_WM'];
        $LMI_PREREQUEST = $_REQUEST['LMI_PREREQUEST'];
        $LMI_HASH = $_REQUEST['LMI_HASH'];


        //Если требуется, подтверждаем заказ
        if($LMI_PREREQUEST==1)
        {
            header("Content-Type: text/html; charset=UTF-8");
            echo "YES";
            die();
        }

        //Проверяем наличие требуемых параметров
        if (!isset ($_REQUEST['LMI_PAYMENT_NO']) ||
        !isset ($_REQUEST['LMI_PAYMENT_AMOUNT']) ||
        !isset ($_REQUEST['LMI_MODE']) ||
        !isset ($_REQUEST['LMI_PAYEE_PURSE']) ||
        !isset ($_REQUEST['LMI_PAYER_WM'])) { die("ERR: Передан не полный набор параметров"); }


        $sql = "SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') ."WHERE pay_code = 'pokupo'";
        $get_pay_config = $GLOBALS['db']->getOne($sql);

        $store = unserialize($get_pay_config);
        $code_list = array();

        foreach ($store as $key=>$value)
        {
            $code_list[$value['name']] = $value['value'];
        }

        $purse = $code_list['pokupo_account']; //Идентификатор магазина для CMS
        $secure = $code_list['pokupo_key'];    //Пароль для уведомлений


        //Проверяем номер магазина
        if($LMI_PAYEE_PURSE!=$purse)
        {
            die("ERR: Id магазина не соответствует настройкам сайта! ");
        }

        //Проверяем сумму заказа
        $sql = "SELECT order_amount FROM " . $GLOBALS['ecs']->table('order_info') ."WHERE order_id = '".$LMI_PAYMENT_NO."'";
        $orderAmount = $GLOBALS['db']->getOne($sql);

        if($orderAmount!=$LMI_PAYMENT_AMOUNT) {
            die("ERR: Сумма оплаты не соответствует сумме заказа!");
        }


        //Расчет контрольного хэша
        $CalcHash = md5($LMI_PAYEE_PURSE . $LMI_PAYMENT_AMOUNT . $LMI_PAYMENT_NO . $LMI_MODE . $LMI_SYS_INVS_NO . $LMI_SYS_TRANS_NO . $LMI_SYS_TRANS_DATE . $secure . $LMI_PAYER_PURSE . $LMI_PAYER_WM);

        if($LMI_HASH == strtoupper($CalcHash))
        {
            //Подтверждение оплаты заказа
            $time = time();
            $sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . "SET `pay_status` = '2', `money_paid` = '$LMI_PAYMENT_AMOUNT', `order_amount` = order_amount-'$LMI_PAYMENT_AMOUNT', `pay_time` = '$time' WHERE order_id = '$LMI_PAYMENT_NO' and `pay_status` = '0'";
            $result = $GLOBALS['db']->query($sql);
            return true;
        }
        else
        {
            return false;
        }
    }
}
?>