<?php
/**
 * ECSHOP
 * ============================================================================
 * Файл выдачи ответов и трансляции запросов для плагинов ECSHOP 2.7.2
 * ============================================================================
 * $Author: zhuwenyuan $
 * $Date: 20013-12-27 17:50:52 +0400 () $
 * $Id: respond.php 14192 2008-02-27 09:50:52Z $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');



/* коды оплаты */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
$st = !empty($_REQUEST['st']) ? trim($_REQUEST['st']) : '';


if (empty($pay_code))
	{
	   $msg = $_LANG['pay_not_exist'];
	}



////////////////////////////////////////////////////////////////
// robokassa                                                  //
////////////////////////////////////////////////////////////////
 if ($pay_code=='robokassa')
  {
		  if ($pay_code=='robokassa' && $st=='ok') {
		    $msg     = $_LANG['pay_success'];
			  }elseif ($pay_code=='robokassa' && $st=='bad')

			  {
			    $msg     = $_LANG['pay_fail'];
		  }else

		  {
		    /* определяем, включены ли оплаты */
		    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
		    if ($db->getOne($sql) == 0)
		    {
		        $msg = $_LANG['pay_disabled'];
		    }
		    else
		    {
		        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

		        /* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
		        if (file_exists($plugin_file))
		        {
		            /* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
		            include_once($plugin_file);

		            $payment = new $pay_code();
		            $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
		            /* отправим майл админу, если оплата успешна */
				    if ($_CFG['service_email'] != '')
				    {
				        $tpl = get_mail_template('robokassa_new_payment');
				        $smarty->assign('order', $order);
				        $smarty->assign('shop_name', $_CFG['shop_name']);
				        $smarty->assign('send_date', date($_CFG['time_format']));
				        $content = $smarty->fetch('str:' . $tpl['template_content']);
				        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
				    }


		        }
		        else
		        {
		            $msg = $_LANG['pay_not_exist'];
		        }
		    }


		  }


	}


////////////////////////////////////////////////////////////////
// Webmoney                                              //
////////////////////////////////////////////////////////////////
	if ($pay_code=='wm')
	 {
		  {
		    /* определяем, включены ли оплаты */
		    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
		    if ($db->getOne($sql) == 0)
		    {
		        $msg = $_LANG['pay_disabled'];
		    }
		    else
		    {
		        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

		        /* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
		        if (file_exists($plugin_file))
		        {
		            /* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
		            include_once($plugin_file);
		            $payment = new $pay_code();
		            $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];

				    /* отправим майл админу, если оплата успешна */
				    if ($_CFG['service_email'] != '')
				    {
				        $tpl = get_mail_template('webmoney_new_payment');
				        $smarty->assign('order', $order);
				        $smarty->assign('shop_name', $_CFG['shop_name']);
				        $smarty->assign('send_date', date($_CFG['time_format']));
				        $content = $smarty->fetch('str:' . $tpl['template_content']);
				        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
				    }

		        }
		        else
		        {
		            $msg = $_LANG['pay_not_exist'];
		        }
		    }
		  }
	 }

////////////////////////////////////////////////////////////////
// liqpay                                              //
////////////////////////////////////////////////////////////////
	if ($pay_code=='liqpay2')
	 {
		  {
		    /* определяем, включены ли оплаты */
		    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
		    if ($db->getOne($sql) == 0)
		    {
		        $msg = $_LANG['pay_disabled'];
		    }
		    else
		    {
		        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

		        /* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
		        if (file_exists($plugin_file))
		        {
		            /* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
		            include_once($plugin_file);
		            $payment = new $pay_code();
		            $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];

				    /* отправим майл админу, если оплата успешна */
				    if ($_CFG['service_email'] != '')
				    {
				        $tpl = get_mail_template('liqpay_new_payment');
				        $smarty->assign('order', $order);
				        $smarty->assign('shop_name', $_CFG['shop_name']);
				        $smarty->assign('send_date', date($_CFG['time_format']));
				        $content = $smarty->fetch('str:' . $tpl['template_content']);
				        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
				    }

		        }
		        else
		        {
		            $msg = $_LANG['pay_not_exist'];
		        }
		    }
		  }
	 }
////////////////////////////////////////////////////////////////
// Яндекс деньги                                              //
////////////////////////////////////////////////////////////////
     if ($pay_code=='yad')
    	{
    	/* определяем, включены ли оплаты */
		    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
		    if ($db->getOne($sql) == 0)
		    {
		        $msg = $_LANG['pay_disabled'];
		    }
		    else
    	    {

			    $pay_code = 'yad';
			    $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';
				/* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
	            if (file_exists($plugin_file))
				{
				/* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
				include_once($plugin_file);
				$payment = new $pay_code();
				$msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];

					/* отправим майл админу, если оплата успешна */
					if ($_CFG['service_email'] != '')
					{
					   $tpl = get_mail_template('yandex_new_payment');
					   $smarty->assign('order', $order);
					   $smarty->assign('shop_name', $_CFG['shop_name']);
					   $smarty->assign('send_date', date($_CFG['time_format']));
					   $content = $smarty->fetch('str:' . $tpl['template_content']);
					   $content=$content.' '.$order;
					   send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}

				}
				else
				{
				$msg = $_LANG['pay_not_exist'];
				}

             }
    	}

////////////////////////////////////////////////////////////////
// Qiwi - платежи через терминалы                             //
////////////////////////////////////////////////////////////////
     if ($pay_code=='qiwi')
    	{
    	/* определяем, включены ли оплаты */
		    $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
		    if ($db->getOne($sql) == 0)
		    {
		        $msg = $_LANG['pay_disabled'];
		    }
		    else
    	    {

			    $pay_code = 'qiwi';
			    $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';
				/* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
	            if (file_exists($plugin_file))
				{
				/* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
				include_once($plugin_file);
				$payment = new $pay_code();
				$msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];

					/* отправим майл админу, если оплата успешна */
					if ($_CFG['service_email'] != '')
					{
					   $tpl = get_mail_template('qiwi_new_payment');
					   $smarty->assign('order', $order);
					   $smarty->assign('shop_name', $_CFG['shop_name']);
					   $smarty->assign('send_date', date($_CFG['time_format']));
					   $content = $smarty->fetch('str:' . $tpl['template_content']);
					   send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
					}

				}
				else
				{
				$msg = $_LANG['pay_not_exist'];
				}

             }
    	}


//**************************************************************
// Pokupo begin *************************************************
//**************************************************************
if ($pay_code=='pokupo')
{
    //Fail url
    if ($st=='fail')
    {
        $msg = $_LANG['pay_fail'];
    }

    //Success url
    elseif ($st=='ok')
    {
        $msg= $_LANG['pay_success'];
    }

    //Notifications url
    else
    {
        /* определяем, включены ли оплаты */
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
        if ($db->getOne($sql) == 0)
        {
            $msg = $_LANG['pay_disabled'];
        }
        else
        {
            $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

            /* проверяем в модуле оплаты данные, оплата прошла корректно или нет */
            if (file_exists($plugin_file))
            {
                /* создаем код в зависимости от завершения оплаты и выводим сообщение клиенту */
                include_once($plugin_file);
                $payment = new $pay_code();
                $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
            }
            else
            {
                $msg = $_LANG['pay_not_exist'];
            }
        }
    }
}
// Pokupo end

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here',    $position['ur_here']);
$smarty->assign('page_title', $position['title']);
$smarty->assign('ur_here',    $position['ur_here']);
$smarty->assign('helps',      get_shop_help());

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());

$smarty->display('respond.dwt');

?>