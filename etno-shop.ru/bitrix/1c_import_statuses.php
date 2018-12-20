<?php
if (!defined("LOG_FILENAME")) {
    define("LOG_FILENAME", __DIR__."/import-log.txt");
}

if ( 60*60*24 < time() - filemtime(LOG_FILENAME) ){
    $fp = fopen(LOG_FILENAME,"w+");
    ftruncate($fp,0);
    fclose($fp);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

AddMessage2Log($_SERVER['REMOTE_ADDR'].PHP_EOL, "export_files");

if('POST' === $_SERVER['REQUEST_METHOD']){
    $FILE_NAME = false;
    $ABS_FILE_NAME = false;
    $WORK_DIR_NAME = false;
    $filename = 'import-statuses-'.md5(time()).'.xml';

    $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . COption::GetOptionString("main", "upload_dir", "upload") . "/import_statuses/";
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
                    //$result = CIBlockXMLFile::UnZip($ABS_FILE_NAME);
                }
                
                if ($result===false)
                {
                    AddMessage2Log("Ошибка парсинга XML.\n","export_files");
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