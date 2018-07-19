<?php
if (!defined("LOG_FILENAME")) {
    define("LOG_FILENAME", __DIR__."/log.txt");
}


if ( 60*60*24 < time() - filemtime(LOG_FILENAME) ){
    $fp = fopen(LOG_FILENAME,"w+");
    ftruncate($fp,0);
    fclose($fp);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$listIP = Array('84.47.183.102', '188.170.15.177');

AddMessage2Log($_SERVER['REMOTE_ADDR'].PHP_EOL, "export_files");

if('POST' === $_SERVER['REQUEST_METHOD'] && in_array($_SERVER['REMOTE_ADDR'], $listIP))
{

    $FILE_NAME = false;
    $ABS_FILE_NAME = false;
    $WORK_DIR_NAME = false;
    $filename = 'files.zip';

    $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . COption::GetOptionString("main", "upload_dir", "upload") . "/export_files/export-files-" . md5(time()) . "/";
    $FILE_NAME = rel2abs($DIR_NAME, "/".$filename);
    if ((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
    {
        $ABS_FILE_NAME = $DIR_NAME.$filename;
        $WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
    }

    $DATA = file_get_contents("php://input");
    $DATA_LEN = defined("BX_UTF")? mb_strlen($DATA, 'latin1'): strlen($DATA);
    
    $files_unzip = false;
    

    if (isset($DATA) && $DATA !== false)
	{
		CheckDirPath($ABS_FILE_NAME);
		if ($fp = fopen($ABS_FILE_NAME, "ab"))
		{
			$result = fwrite($fp, $DATA);
			if ($result === $DATA_LEN)
			{
				AddMessage2Log("success".PHP_EOL, "export_files");
                if (!CModule::IncludeModule('iblock'))
                {
                    AddMessage2Log("Модуль Информационных блоков не установлен.".PHP_EOL,"export_files");
                }    
                else 
                {
                    AddMessage2Log(substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1).PHP_EOL,"export_files");
                    $result = CIBlockXMLFile::UnZip($ABS_FILE_NAME);
                }
                
                if ($result===false)
                {
                    AddMessage2Log("Ошибка распаковки архива.".PHP_EOL,"export_files");
                }
                elseif ($result===true)
                {
                    $ABS_FILE_NAME = false;
                    $files_unzip = true;
                    AddMessage2Log("Распаковка архива завершена.".PHP_EOL,"export_files");
                }
                else
                {
                    AddMessage2Log("Идет распаковка архива.".PHP_EOL,"export_files");
                }
			}
			else
			{
				AddMessage2Log("Ошибка записи в файл".PHP_EOL, "export_files");
			}
		}
		else
		{
			AddMessage2Log("Ошибка открытия файла для записи.".PHP_EOL, "export_files");
		}
	}
	else
	{
		AddMessage2Log("Ошибка чтения HTTP данных.".PHP_EOL, "export_files");
    }

    if ($files_unzip) 
    {
        $list_files = Array
        (
            '8458' => 'metall.xls',
            '8114' => 'nelikv.xls',
            '8457' => 'nelikv_metall.xls',
            '8113' => 'nnp.xls',
            '8116' => 'pir.xls',
            '8115' => 'sklad.xls'
        );

        define('PROPERTY_CODE','FILE');
        define('IBLOCK_ID',58);
        define('SECTION_ID',129);
        foreach ($list_files as $id => $file) 
        {

            if (file_exists($WORK_DIR_NAME.$file))
            {
                $TMPFILE = Array
                (
                    "name" => basename($WORK_DIR_NAME.$file),
                    "type" => filetype($WORK_DIR_NAME.$file),
                    "tmp_name" => $WORK_DIR_NAME.$file,
                    "error" => 0,
                    "size" => filesize($WORK_DIR_NAME.$file)
                );
                
                $PROPERTY_VALUES = Array
                (
                    "FILE" => Array('VALUE' => $TMPFILE, 'DESCRIPTION' => $TMPFILE['name'])
                );

                CIBlockElement::SetPropertyValuesEx($id, IBLOCK_ID, $PROPERTY_VALUES );

                AddMessage2Log("Файл {$file} сохранен в єлемент ", "export_files");

                // Send mail

                $res = CIBlockElement::GetByID($id)->GetNext();
                if ($res) {
                    $arFileds['IBLOCK_ID'] = IBLOCK_ID;
                    $arFields["IBLOCK_SECTION"][0] = SECTION_ID;
                    $arFields["ID"] = $id;
                    $arFields["NAME"] = $res['NAME'];
                    $arFields["DETAIL_TEXT"] = $res['DETAIL_TEXT'];

                    AddMessage2Log("Входной массив ".print_r($arFileds,TRUE), "send_mail");

                    saleEmailSend($arFileds);

                    AddMessage2Log("Письмо отправленно ".$id, "send_mail");

                } else {
                    AddMessage2Log("Ошибка получения данных ".$id, "send_mail");
                }
                
            } 
            else {
                AddMessage2Log("Файл не найден {$file}".PHP_EOL, "export_files");
            }
            
        }
        
    }

} 
else 
{
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>