<?php
set_time_limit(3600);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');

if (!defined("LOG_FILENAME")) {
    define("LOG_FILENAME", __DIR__."/import-log.txt");
}

define("CATALOG_IBLOCK_ID", 4);

if ( 60*60*24 < time() - filemtime(LOG_FILENAME) ){
    $fp = fopen(LOG_FILENAME,"w+");
    ftruncate($fp,0);
    fclose($fp);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

AddMessage2Log($_SERVER['REMOTE_ADDR'].PHP_EOL, "export_files");
AddMessage2Log(print_r($_SERVER, TRUE).PHP_EOL, "export_files");

if('POST' === $_SERVER['REQUEST_METHOD']){
    $FILE_NAME = false;
    $ABS_FILE_NAME = false;
    $WORK_DIR_NAME = false;
    $filename = 'import-statuses-'.md5(time()).'.xml';

    $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . '/' . COption::GetOptionString("main", "upload_dir", "upload") . "/import_statuses/";
    $FILE_NAME = rel2abs($DIR_NAME, "/".$filename);

    if ((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
    {
        $ABS_FILE_NAME = $DIR_NAME.$filename;
        $WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
    }

    $DATA = file_get_contents("php://input");
    $DATA_LEN = defined("BX_UTF")? mb_strlen($DATA, 'latin1'): strlen($DATA);

    if (isset($DATA) && $DATA !== false)
	{
		CheckDirPath($ABS_FILE_NAME);
		if ($fp = fopen($ABS_FILE_NAME, "ab"))
		{
			$result = fwrite($fp, $DATA);
			if ($result === $DATA_LEN)
			{
				AddMessage2Log("success\n", "export_files");
                if (!CModule::IncludeModule('iblock'))
                {
                    AddMessage2Log("Модуль Информационных блоков не установлен.\n","export_files");
                }    
                else 
                {
                    AddMessage2Log(substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1)."\n","export_files");

                    $xml = new CDataXML();

                    if ($xml) {
                        $base64_encode = file_get_contents($ABS_FILE_NAME);
                        $xml_string = $result = base64_decode($base64_encode);
                        if ($base64_encode && $xml_string) {
                            $xml->LoadString($xml_string);

                            if ($node = $xml->SelectNodes('/Товары')) {
                                $data = [];
                                foreach ($node->children() as $index => $childNode) {
                                    $arrayChild = $childNode->__toArray();
                                    // Артикул
                                    // Состояние
                                    $articule = $arrayChild['#']['Артикул'][0]['#'];
                                    $statuses = $arrayChild['#']['Состояние'][0]['#'];
                                    $data[$articule] = $statuses;

                                    $articules = array_keys($data);

                                    $arOreder = [];
                                    $arFilter = [
                                        "IBLOCK_ID" => CATALOG_IBLOCK_ID,
                                        "PROPERTY_ARTICLE" => $articules,
                                    ];
                                    $arSelect = ["PROPERTY_ARTICLE"];

                                    $result = CIBlockElement::GetList($arOreder, $arFilter, false, [], $arSelect);
                                    $ids = [];
                                    while($ob = $result->GetNextElement()) {
                                        $arFields = $ob->GetFields();  
                                        $ids[] = $ID = $arFields['ID'];
                                        $arProps = $ob->GetProperties();
                                        CIBlockElement::SetPropertyValuesEx($ID, CATALOG_IBLOCK_ID, [
                                            "PROPERTY_DELIVERY_TYPE_TEST" => $data[$arProps['PROPERTY_ARTICLE']]
                                        ]);
                                    }

                                    AddMessage2Log(implode('|', $ids)."\n","update goods");        
                                }

                            } else {
                                AddMessage2Log("Не удалось получить список товаров .\n","export_files");        
                            }
                            
                        } else {
                            AddMessage2Log("Ошибка чтения файла xml .\n","export_files");    
                        }
                    } else {
                        AddMessage2Log("Ошибка создания объекта xml .\n","export_files");
                    }
                }
                
                if ($result===false)
                {
                    AddMessage2Log("Ошибка парсинга чтения base64.\n","export_files");
                }
                elseif ($result===true)
                {
                    $ABS_FILE_NAME = false;
                    $files_unzip = true;
                    AddMessage2Log("Файл прочитан.\n","export_files");
                }
                else
                {
                    AddMessage2Log("Идет распаковка архива.\n","export_files");
                }
			}
			else
			{
				AddMessage2Log("Ошибка записи в файл\n", "export_files");
			}
		}
		else
		{
			AddMessage2Log("Ошибка открытия файла для записи.\n", "export_files");
		}
	}
	else
	{
		AddMessage2Log("Ошибка чтения HTTP данных.\n", "export_files");
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>