<?php



 class rss{
     public $url ;
     public $mass_news ;

     //стартуем
     function __construct($url) {
        $this->url=$url;
         $this->get5new();
     }

     //получаем 5 новостей
     public  function get5new(){
        $response_xml_data = file_get_contents($this->url);
        $data = simplexml_load_string($response_xml_data);
        $count=0;
        foreach($data->channel->item as $value){
            if($count==5){ continue; }
            $mass_temp=array();
            $mass_temp['name']=(string)$value->title;
            $mass_temp['href']=(string)$value->link;
            $mass_temp['anons']=(string)$value->description;
            $this->mass_news[]=$mass_temp;
            $count++;
        }
    }

    // Отображает с проверкой на коммандную строку
    public function show(){
        $sapi = php_sapi_name();
        if ($sapi=='cli') {
            foreach ($this-> mass_news as $value){
                print $value['name'].' '.$value['href'].' '.$value['anons'].'/n';
            }
        }else{
            echo 'Ошибка! Запуск возможен только с командной строки';
        }
    }
 }

$rss=new rss('https://lenta.ru/rss');
$rss->show();


