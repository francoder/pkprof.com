<?php

namespace controllers\front;

use GuzzleHttp\Client;

class AppKassy extends AppContent {
    protected $table = 'tovar';
    protected $tpldir = 'tmart';
    protected $layout = 'layout.html';
    protected $page_layout = 'inner';

    protected $api = 'https://api.kassy.ru/v3/';
    protected $agent_id = 'agent_id';
    protected $secret_key = 'secret_key';
    protected $client = null;
    protected $xml_show = '<?xml version="1.0" encoding="utf-8"?><request db="barnaul" module="table_show" format="json"><filter id="" db="" state="1" /><auth id="pkprof.com" /></request>';
    protected $xml_event = '<?xml version="1.0" encoding="utf-8"?><request db="barnaul" module="table_event" format="json"><filter id="" show_type_id="" show_id="%s" rollerman_id="" building_id="" hall_id="" date_from="%s" date_to="1561445735" is_recommend="" state="1" /><auth id="pkprof.com" /></request>';
    protected $xml_event_view = '<?xml version="1.0" encoding="utf-8"?><request db="barnaul" module="page_event_view" format="json"><filter id="%s" /><auth id="pkprof.com" /></request>';

    public function __construct() {
        parent::__construct();
        $this->client = new Client();
    }
    
    protected function requestData($xml) {
        $sign = md5($xml . $this->secret_key);
        $cache = \Cache::instance();
        if (!$cache->exists('kassy_'.$sign, $res)) {
            $params = ['form_params' => ['xml' => $xml, 'sign' => $sign]];
            $request = $this->client->request('POST', $this->api, $params);
            $contents = $request->getBody()->getContents();
            $res = json_decode($contents, true);
            $cache->set('kassy_'.$sign, $res, 60*10); // Кэшируем ответ
        }
        return $res;
    }
    
    public function home() {

        // События
        $table_event = $this->requestData(sprintf($this->xml_event, '', time()));
        $events = $table_event['content'];

        // Шоу
        $table_show = $this->requestData($this->xml_show);
        $shows = $table_show['content'];
        $shows_data = array_column($shows, NULL, 'id');
        foreach ($events as $k => $v) {
            $events[$k]['title'] = $shows_data[$v['show_id']]['title'];
            $events[$k]['age_rating'] = $shows_data[$v['show_id']]['age_rating'];
            $events[$k]['is_sale'] = $shows_data[$v['show_id']]['is_sale'];
            $events[$k]['image'] = $shows_data[$v['show_id']]['image'];
        }

        // Описания для шоу
        $xml = sprintf($this->xml_event_view, '');
        $table_event_view = $this->requestData($xml);
        $event_view = $table_event_view['content']['show'];
        $event_data = array_column($event_view, NULL, 'id');
        foreach ($events as $k => $v) {
            $events[$k]['descr'] = $event_data[$v['show_id']]['descr'];
        }

        $this->setLayout();
        $this->layout()->setProperties(['title' => 'Билеты']);
        $this->layout()->setSettings();
        $this->layout()->addLevelToBreadCrumbs(array(
            '/' => "Главная",
            '/katalog' => "Каталог товаров",
        ));

        // Топ мероприятий
        if($this->exists('site.settings.top', $top_ids)) {
            $top_ids = explode(',', $top_ids);
            $list = [];
            foreach ($top_ids as $id) {
                if (isset($shows_data[$id])) {
                    $list[$id] = $shows_data[$id];
                }
            }
            $this->set('top_shows', $list);
        }

        $this->set('html.inc', $this->tpldir.'/kassy/list.html');
        $this->set('list_event', $events);
        $this->set('list_show', $table_show);
    }

    public function one() {
        if (!$this->exists('PARAMS.id', $show_id)) {
            $this->error(404);
        }
        $xml = sprintf($this->xml_event, $show_id, 0);
        $table_event = $this->requestData($xml);

        $xml = sprintf($this->xml_event_view, $table_event['content'][0]['id']);
        $list_event = $this->requestData($xml);

        $this->set('list_event', $list_event);

        $this->setLayout();
        $this->layout()->setProperties( [
                'af_headtitle' => 'Информация о мероприятии',
                'title' => $list_event['content']['show'][0]['title']
            ]);
        $this->layout()->addLevelToBreadCrumbs(array(
            '/' => "Главная",
            '/katalog' => "Каталог товаров",
            '/katalog/bilety' => "Билеты"
        ));

        $this->set('html.inc', $this->tpldir.'/kassy/one.html');
    }
}
