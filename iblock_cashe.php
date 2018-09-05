<?php


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


class iblockCashe{

    public $cache_time ;
    public $cache_path ;
    public $cache ;

    // начало
    function __construct($cache_time=100 ,$cache_path='cashenew') {
        $this->cache_time=$cache_time;
        $this->cache_path=$cache_path;
        $this->cache= new CPHPCache();
    }

    //получаем данные в массив
    public function getMassData( $arOrder,   $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields ){
       //получаем массив с данными
        CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
        $arMassList=array();
         while($ar_res = $res->Fetch())
          {
              $arMassList[]=$ar_res;
          }
        return $arMassList;
    }

    // аналог getList- получаем массив с данными (все параметры как у get lista)
    public function GetCashedListData(array $arOrder = Array("SORT"=>"ASC"),  $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false,  $arSelectFields = Array())
    {
       //делаем id кеш-массива
        $mass= array('O'=> $arOrder, 'F'=> $arFilter, 'G'=> $arGroupBy, 'N'=> $arNavStartParams, 'S'=> $arSelectFields
        );
        $cache_id=md5(json_encode($mass));
        $cache_time = $this->cache_time;
        $cache_path=$this->cache_path;

        // проверка на наличие кеша  и истекшее время
        if ($cache_time > 0 && $this->cache->InitCache($cache_time, $cache_id, $cache_path))
        {
            $res = $this->cache->GetVars();
            if (is_array($res["arMassList"]) && (count($res["arMassList"]) > 0)){
                $arMassList = $res["arMassList"];
               // echo 'Берем данные из кеша';
            }
        }
        //если пустой кешь или прошло время , получаем данные из базы
        if (!is_array($arMassList))
        {
            $arMassList=$this->getMassData( $arOrder ,  $arGroupBy ,  $arNavStartParams , $arSelectFields );
           // echo 'Не кешированный!!!';
            if ($cache_time > 0)
            {
                $this->cache->StartDataCache($cache_time, $cache_id, $cache_path);
                $this->cache->EndDataCache(array("arMassList"=>$arMassList));
            }
        }
        return $arMassList;
    }

    //очистка кеша
    public function ClearCashe(){
        $this->cache->CleanDir( $this->cache_path );
    }
}

// стартуем, задаем начальные параметры
$iblock_obj=new iblockCashe(150 ,'mypath2');

//получаем даные
$arResult=$iblock_obj->GetCashedListData(array(), array('ID'=>2) ,false ,false, array('ID','NAME'));
print_r($arResult);
//Очистка кеша, по необходимости
//$iblock_obj->ClearCashe();

 die();

