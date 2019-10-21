<?php
namespace controllers;

use models\content\ContentRecord;
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;
use Voronkovich\SberbankAcquiring\OrderStatus;

class AppOrder extends Content
{
    protected $table = 'order';
    protected $tpldir = 'tmart';
    protected $layout = 'layout.html';
    protected $page_layout = 'inner';

    protected $username = 'pkprof-api';
    protected $password = 'FBSPYhez{Ti5P6J4';

    public function listView() {
        parent::listView();
        $this->html['page_title'] = 'Мои заказы';
        $this->html['head_title'] = 'Мои заказы';
        $this->set('html.inc', $this->tpldir.'/lk/order/items.html');
//        $this->print_r('data.list.subset',1);
        if ($this->exists('GET.oid', $oid) && $this->exists('GET.oamount', $oamount)) {
            $this->createOrder($oid, $oamount);
        }
    }

    protected function prepareFilter() {

    }

    protected function createOrder($oid, $oamount) {
        $client = new Client([
            'userName' => $this->username,
            'password' => $this->password,
            'language' => 'ru',
            'apiUri' => Client::API_URI,
            'httpMethod' => 'GET',
        ]);

// Required arguments
        $orderId    = (int) $oid.'-'.time();
        $orderAmount= (float) $oamount * 100;
        $returnUrl  = getenv('SB_RETURL');

// You can pass additional parameters like a currency code and etc.
        $params['currency'] = Currency::RUB;
        $params['failUrl']  = getenv('SB_FAILURL');

        $result = $client->registerOrder($orderId, $orderAmount, $returnUrl, $params);

        $paymentFormUrl = $result['formUrl'];

        header('Location: ' . $paymentFormUrl);
    }

    public function paymentSuccess() {
        if (!$this->exists('GET.orderId', $oid)) {
            return $this->error(403, "Доступ запрещен");
        }
        $client = new Client([
            'userName' => $this->username,
            'password' => $this->password,
            'language' => 'ru',
            'apiUri' => Client::API_URI,
            'httpMethod' => 'GET',
        ]);
        $result = $client->getOrderStatusExtended($oid);
        $orderNumber = $result['orderNumber'];
        if(!OrderStatus::isDeclined($result['orderStatus'])) {
            ContentRecord::instance('order')->update(['id' => stristr($orderNumber, '-', true), 'fko_order_status' => 3]);
        }
        $this->html['page_title'] = 'Оплата прошла успешно';
        $this->html['head_title'] = 'Оплата произведена';
        $this->set('html.inc', $this->tpldir.'/lk/order/payment-success.html');
    }

    public function paymentFailure() {
        if (!$this->exists('GET.orderId')) {
            return $this->error(403, "Доступ запрещен");
        }
        $this->html['page_title'] = 'Во время оплаты произошла ошибка';
        $this->html['head_title'] = 'Ошибка при оплате';
        $this->set('html.inc', $this->tpldir.'/lk/order/payment-failure.html');
    }
}