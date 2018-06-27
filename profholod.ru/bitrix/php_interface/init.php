<?
@require_once 'include/autoload.php';
define("RE_SITE_KEY","6Le6EFMUAAAAAKaOYAh35fy1zJG3AaHXb3Qa_my0");
define("RE_SEC_KEY","6Le6EFMUAAAAADwikgQeCmFzTVwr4muEgAUqmWuU");

AddEventHandler("main", "OnBeforeEventAdd", array("MainHandlers", "OnBeforeEventAddHandler")); 
class MainHandlers{ 
   function OnBeforeEventAddHandler($event, $lid, $arFields){ 
      if($event == "CONSTRUCTOR_NEW"){ 
         require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/classes/mail_attach.php"); 
         $file = $arFields["FILE_PATH"];
         //$fileCont = file_get_contents($_SERVER["DOCUMENT_ROOT"].$arFile);
         //$fileDir = dirname($arFile);
         //$fileName = str_replace("_", "", basename($arFile));
         //$origFileName = $fileDir."/".$fileName;
         //$origFile = fopen($_SERVER["DOCUMENT_ROOT"].$origFileName, 'w');
         //fwrite($origFile, $fileCont);
         SendAttache($event, $lid, $arFields, $file); 
         $event = 'null'; $lid = 'null'; 
      } 
   } 
}

AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("Resizer", "ResizeElementProperty"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("Resizer", "ResizeElementProperty"));
AddEventHandler("iblock", "OnAfterIBlockPropertyUpdate", Array("Resizer", "ResizeElementProperty"));

class Resizer
{
	function ResizeElementProperty(&$arFields) {
	
		if($arFields['IBLOCK_ID']=='21' || $arFields['IBLOCK_ID']=='22') {
		
			$arFile = array(); 
			$i=0;
			$res=CIBlockElement::GetProperty($arFields['IBLOCK_ID'], $arFields['ID'], array("value_id" => "asc"), Array("CODE"=>"MORE_PHOTO"));
			 while ($ob = $res->GetNext())
			    {
				
					$Img_Id = CFile::CopyFile($ob['VALUE']); 
			   		$arrFile = CFile::MakeFileArray($Img_Id);
					
			                $get_width=CFile::GetByID($Img_Id);
			                $get_width_f = $get_width->Fetch();
			
			                $width = (71/$get_width_f['HEIGHT'])*$get_width_f['WIDTH']; 
			
					if($arrFile) {			
						$arNewFile = CIBlock::ResizePicture($arrFile, array(
						"WIDTH" => $width,
						"HEIGH" => 71,
						"METHOD" => "resample",
						"COMPRESSION" => 80,
						));		
			        
						$arFile[$i]=array("VALUE"=>$arNewFile, "DESCRIPTION"=>$ob['DESCRIPTION']);
					} 
				$i++;
				}
				
				 $res = CIBlockElement::GetByID($arFields['ID']);
				if($ar_res = $res->GetNext()) {
							$Img_Id = CFile::CopyFile($ar_res["DETAIL_PICTURE"]); 
							$arrFile = CFile::MakeFileArray($Img_Id);
			
			                $get_width=CFile::GetByID($Img_Id);
			                $get_width_f = $get_width->Fetch();
			
			                $width = (71/$get_width_f['HEIGHT'])*$get_width_f['WIDTH']; 
			
					 if($arrFile) {			
							$arNewFile = CIBlock::ResizePicture($arrFile, array(
							"WIDTH" => $width,
							"HEIGH" => 71,
							"METHOD" => "resample",
							"COMPRESSION" => 80,
							));		
				        }
					$d_p=array("VALUE" => $arNewFile,"DESCRIPTION"=>$ob['DESCRIPTION']);					
				}			
					
				CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFields['IBLOCK_ID'], array("DETAIL_PHOTO_PREW" => $d_p));					
				CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFields['IBLOCK_ID'], array("MORE_PHOTO_PREW" => $arFile));	
		}		
	}
}
class WS{
	public function ValidateEmailorPhone($string){
		if (!preg_match('/[0-9a-z_]+@[0-9a-z_^\.-]+\.[a-z]{2,3}/i', $string)){
			$err_mail="Y";
		}else{$err_mail="N";}
		if (preg_match("/[^0123456789+ \\-\\(\\)]/i", $string))  {
			$err_phone="Y";
		}else{$err_phone="N";}
		if($err_phone=="Y" && $err_mail=="Y"){
			return false;
		}else{return true;}
	}
	public function ShowByLang($property_code, $arArray) {
		
		
		if ($property_code=='SHOW_PROPERTY') {
			$arr_vals=explode('|', $arArray);
			
			if (count($arr_vals)>1) {
				foreach($arr_vals as $values) {
					$arr_vals_2=explode('_', $values);
					
					if ($arr_vals_2[1]==LANGUAGE_ID || $arr_vals_2[1]==strtoupper(LANGUAGE_ID)) {	
						return $arr_vals_2[0];
					}
				}
			}
			else {
				return $arArray;
			}
		}
	
		if(LANGUAGE_ID=='ru') {
			if ($arArray['PROPERTY_'.$property_code.'_VALUE']!='') {
				return $arArray['PROPERTY_'.$property_code.'_VALUE'];
			}
			elseif ($arArray['~UF_'.$property_code]!='') {
				return $arArray['~UF_'.$property_code];
			}				
			elseif ($arArray['UF_'.$property_code]!='') {
				return $arArray['UF_'.$property_code]["VALUE"];
			}
			elseif($arArray['PROPERTIES'][$property_code]['VALUE']!='') {
				return $arArray['PROPERTIES'][$property_code]['VALUE'];
			}
			elseif($arArray['DISPLAY_PROPERTIES'][$property_code]['VALUE']!='') {
				return $arArray['DISPLAY_PROPERTIES'][$property_code]['VALUE'];
			}				
		}
		else {
			if ($arArray['PROPERTY_'.LANGUAGE_ID.'_'.$property_code.'_VALUE']!='') {
				return $arArray['PROPERTY_'.LANGUAGE_ID.'_'.$property_code.'_VALUE'];
			}
			if ($arArray['PROPERTY_'.strtoupper(LANGUAGE_ID).'_'.$property_code.'_VALUE']!='') {
				return $arArray['PROPERTY_'.strtoupper(LANGUAGE_ID).'_'.$property_code.'_VALUE'];
			}	
			elseif ($arArray['USER_FIELDS']['UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code]['VALUE']!='') {
				return $arArray['USER_FIELDS']['UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code]['VALUE'];
			}	 
			elseif ($arArray['~UF_'.LANGUAGE_ID.'_'.$property_code]!='') {
				return $arArray['~UF_'.LANGUAGE_ID.'_'.$property_code];
			}	
			elseif ($arArray['~UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code]!='') {
				return $arArray['~UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code];
			}
			elseif ($arArray['UF_'.LANGUAGE_ID.'_'.$property_code]!='') {
				return $arArray['UF_'.LANGUAGE_ID.'_'.$property_code]["VALUE"];
			}	
			elseif ($arArray['UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code]!='') {
				return $arArray['UF_'.strtoupper(LANGUAGE_ID).'_'.$property_code]["VALUE"];
			}
			elseif($arArray['PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['~VALUE']['TEXT']!='' && is_array($arArray['PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['~VALUE'])) {
				return $arArray['PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['~VALUE']['TEXT'];
			}			
			elseif($arArray['PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['VALUE']!='') {
				return $arArray['PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['VALUE'];
			}
			elseif($arArray['PROPERTIES'][strtoupper(LANGUAGE_ID).'_'.$property_code]['VALUE']!='') {
				return $arArray['PROPERTIES'][strtoupper(LANGUAGE_ID).'_'.$property_code]['VALUE'];
			}
			elseif($arArray['DISPLAY_PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['VALUE']!='') {
				return $arArray['DISPLAY_PROPERTIES'][LANGUAGE_ID.'_'.$property_code]['VALUE'];
			}				
			elseif($arArray[LANGUAGE_ID.'_'.$property_code]!='') {
				return $arArray[LANGUAGE_ID.'_'.$property_code];
			}
		}
		return $arArray[$property_code];					
	}
	
	public function GetList($arPar, $cache_time='600') {		
	 
	
		$obCache = new CPHPCache;	
		$cache_id=serialize($arPar);
		if($obCache->InitCache($cache_time, $cache_id, '/cache_arr_list/')) {
			$vars = $obCache->GetVars();
			$res_array=$vars['res_array'];
			$obCache->Output();
		}
		elseif($obCache->StartDataCache()) {
			CModule::IncludeModule('iblock');
			
			$arSort=$arPar[0];
			$arFilter=$arPar[1];
			$arGroupBy=$arPar[2];
			$arNavStartParams=$arPar[3];
			$arSelectFields=$arPar[4];
			
			$res = CIBlockElement::GetList($arSort, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
			while($ob = $res->GetNext()){
				$res_array['ALL'][$ob['ID']]=$ob;
				$res_array['IDS'][]=$ob['ID'];
			} 
			
			$obCache->EndDataCache(array("res_array" => $res_array)); 
		}
		return $res_array;						
	}
	
	public function GetOptionStr($array){
		
		if(is_array($array) && !empty($array)){
		
			foreach($array as $key => $val){
				$str .='<option value="'.$val.'">'.$val.'</option>';
			}

			return $str;
		}
		else{
			return false;
		}
	}
	
	/*list user*/
	public function GetListUser($arPar, $cache_time='360000'){
		$obCache = new CPHPCache;	
		$cache_id=serialize($arPar);
		if($obCache->InitCache($cache_time, $cache_id, '/cache_arr_list_user/')) {
			$vars = $obCache->GetVars();
			$res_array=$vars['res_array'];
			$obCache->Output();
		}
		elseif($obCache->StartDataCache()) {
			CModule::IncludeModule('main');
			
			$arSort=$arPar[0];
			$arOrder=$arPar[1];
			$arFilter=$arPar[2];
			$arParameters=$arPar[3];
			
			$res = CUser::GetList($arSort, $arOrder, $arFilter, $arParameters);
			$res_array["EMAIL_SEND"] = '';
			while($ob = $res->Fetch()){
				$res_array['ALL'][$ob['ID']]=$ob;
				$res_array['IDS'][]=$ob['ID'];
				$res_array["EMAIL_SEND"] .= $ob["EMAIL"].",";
			} 
			$obCache->EndDataCache(array("res_array" => $res_array)); 
		}
		return $res_array;						
	}
	
	public function ShowH1($property_id, $alt_property=false, $default_value=false) {
		global $APPLICATION;
		$val=$APPLICATION->GetProperty($property_id);
		if ($val!='') {
			return $val;
		}
		else {
			return ($alt_property=='title')?$APPLICATION->GetTitle():$APPLICATION->GetProperty($alt_property);
		}
	}
	
	public function DShowProperty($property_id, $alt_property=false, $default_value=false) { 
		global $APPLICATION; 
		$APPLICATION->AddBufferContent(Array('WS', "ShowH1"), $property_id, $alt_property, $default_value); 
	}
	
}

function GenerateLink($WEB_FORM_ID, $RESULT_ID) {	

	$file_id=intval($_REQUEST['form_hidden_42']);	
	
	if ($file_id>0) {		
		$id_of_download=time()*2;
	
		CFormResult::SetField($RESULT_ID, "id_of_download", $id_of_download);	
		
		$link='http://'.SITE_SERVER_NAME.'/technical_documentation/catalogs/download/'.$id_of_download.'/';
		
		CFormResult::SetField($RESULT_ID, "link", $link);		
	}
}

AddEventHandler('form', 'onAfterResultAdd', 'GenerateLink');

//Оповещения о изменении елементов
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("Change", "new_element"));
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", Array("Change", "delete_element"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("Change", "change_element"));
AddEventHandler("iblock", "OnAfterIBlockSectionAdd", Array("Change", "new_section"));
AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", Array("Change", "delete_section"));
AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate", Array("Change", "change_section"));

class Change{

	function GetEmail(){
		$param = array(
			($by="email"),
			($order="asc"),
			array("GROUPS_ID" => array(9)),
			array("FIELDS"=>array("ID","LOGIN","EMAIL","NAME")),
		);

		$email = WS::GetListUser($param);
		
		return $email["EMAIL_SEND"];
	}


	function new_element(&$arFields){
		
		 
		
		if(is_array($arFields) && $arFields["NAME"]!=""){
		
			$email = Change::GetEmail();
			
			$link = CIBlockElement::GetByID($arFields["RESULT"])->GetNext();
			if($link["DETAIL_PAGE_URL"]!=""){
				$link = $_SERVER["SERVER_NAME"].$link["DETAIL_PAGE_URL"];

				
				$arFieldsMail = array(
					"EMAIL" => $email,
					"LINK" => $link,
					"NAME" => $arFields["NAME"],
				);   
				 	
				CEvent::Send(1869,'s1',$arFieldsMail,"N",64);
			}
		}
	}

	function delete_element($ID){
		
		$email = Change::GetEmail();
		
		$arElement = CIBlockElement::GetByID($ID)->GetNext();
		$link = $_SERVER["SERVER_NAME"].$arElement["DETAIL_PAGE_URL"];
		
		$arFieldsMail = array(
			"EMAIL" => $email,
			"LINK" => $link,
			"NAME" => $arElement["NAME"],
		); 
		
		CEvent::Send(1869,'s1',$arFieldsMail,"N",65);
	}
	
	function change_element(&$arFields){
	
		global $USER;
		 
	
		$email = Change::GetEmail();
	
		$arElement = CIBlockElement::GetList(
			array(),
			array("ID"=>$arFields["ID"]),
			false,
			array("nPageSize"=>1),
			array("ID","IBLOCK_ID","ACTIVE","NAME","DETAIL_TEXT","PREVIEW_TEXT","IBLOCK_SECTION_ID","DETAIL_PAGE_URL","DATE_CREATE","IPROPERTY_TEMPLATES"))->GetNextElement();

		$arElement = $arElement->GetFields();

		$link = $_SERVER["SERVER_NAME"].$arElement["DETAIL_PAGE_URL"];
  
		//если прошло 10 часов после создания элемента
		if((time()-strtotime($arElement["DATE_CREATE"]))>36000){
			$text = '';
			
			//active
			if($arFields["ACTIVE"]!=$arElement["ACTIVE"]){
				switch($arFields["ACTIVE"]){
					case "Y":
						$text .= "<h5>Элемент:</h5> Активирован<br />";
						break;
					case "N":
						$text .= "<h5>Элемент:</h5> Деактивирован<br />";
						break;
				}
			}
			
			if(strlen(trim(strip_tags($arFields["PREVIEW_TEXT"])))!=strlen(trim(strip_tags($arElement["PREVIEW_TEXT"])))/*|| $arFields["PREVIEW_TEXT_TYPE"]!=$arElement["PREVIEW_TEXT_TYPE"]*/){
				$text .="<hr />";
				$text .= "<h5>Старый текст анонса:</h5>";
				$text .= $arElement["~PREVIEW_TEXT"];
				$text .= "<h5>Новый текст анонса:</h5>";
				$text .= $arFields["PREVIEW_TEXT"];
			}
			
			if(strlen(trim(strip_tags($arFields["DETAIL_TEXT"])))!=strlen(trim(strip_tags($arElement["DETAIL_TEXT"]))) /*|| $arFields["DETAIL_TEXT_TYPE"]!=$arElement["DETAIL_TEXT_TYPE"]*/){
				$text .= "<br />";
				$text .="<hr />";
				$text .= "<h5>Старый текст описания:</h5>";
				$text .= $arElement["~DETAIL_TEXT"];
				$text .= "<h5>Новый текст описания:</h5>";
				$text .= $arFields["DETAIL_TEXT"];
			}
			
			$change_sec=0;
			
			//many sections			
			if(count($arFields["IBLOCK_SECTION"])>0){
				$db_old_groups = CIBlockElement::GetElementGroups($arFields["ID"], true);
				
				//get all sections for Iblock element
				while($ar_group = $db_old_groups->Fetch()){
					$all_section[] = $ar_group["ID"];
				}
				
				sort($arFields["IBLOCK_SECTION"]);
				sort($all_section);
				
				if(count($all_section)!=count($arFields["IBLOCK_SECTION"])){
					$change_sec=1;
				}
				else{
					foreach($all_section as $key => $sec_id){
						if($sec_id!=$arFields["IBLOCK_SECTION"][$key]){
							$change_sec=1;
							break;
						}
					}
				}
			}

			if($change_sec==1){
				
				$text .="<hr />";
			
				$get_all_section = array_merge($all_section,$arFields["IBLOCK_SECTION"]);

				$res = CIBlockSection::GetList(
					array(),
					array("ID"=>$get_all_section),
					false,
					array("ID","IBLOCK_ID","NAME")
					);
					
				while($arFields_sec = $res->GetNext()){
					$all_sec_name[$arFields_sec["ID"]]["NAME"] = $arFields_sec["NAME"];
				}
				
				$text .= "<h5>Старые категории:</h5> <br />";
				foreach($all_section as $sec_id){
					$text .= $all_sec_name[$sec_id]["NAME"]."<br />";
				}
				
				$text .= "<h5>Новые категории:</h5> <br />";
				foreach($arFields["IBLOCK_SECTION"] as $sec_id){
					$text .= $all_sec_name[$sec_id]["NAME"]."<br />";
				}
			}
			
			//if("IPROPERTY_TEMPLATES")
				
			
			
			if($text!=""){
				$arFieldsMail = array(
					"EMAIL" => $email,
					"LINK" => $link,
					"NAME" => $arFields["NAME"],
					"LOGIN_CHANGE" => $USER->GetLogin(),
					"DATE_CHANGE" => date("d.m.Y H:i:s"),
					"TEXT" => $text,
				); 
			
			
				CEvent::Send(1869,'s1',$arFieldsMail,"N",66);
			}
			
		}
	}
	
	function new_section(&$arFields){
		
		$email = Change::GetEmail();
		
		$link = CIBlockSection::GetByID($arFields["RESULT"])->GetNext();
		$link = $_SERVER["SERVER_NAME"].$link["SECTION_PAGE_URL"];
		
		$arFieldsMail = array(
			"EMAIL" => $email,
			"LINK" => $link,
			"NAME" => $arFields["NAME"],
		); 
		
		CEvent::Send(1871,'s1',$arFieldsMail,"N",67);
	}

	function delete_section($ID){
		
		$email = Change::GetEmail();
		
		$arSection = CIBlockSection::GetByID($ID)->GetNext();
		$link = $_SERVER["SERVER_NAME"].$arSection["SECTION_PAGE_URL"];
		
		$arFieldsMail = array(
			"EMAIL" => $email,
			"LINK" => $link,
			"NAME" => $arSection["NAME"],
		); 
		
		CEvent::Send(1871,'s1',$arFieldsMail,"N",68);
	}
	
	function change_section(&$arFields){
		
		global $USER;
	
		$email = Change::GetEmail();
	
		$arSection = CIBlockSection::GetList(
			array(),
			array("ID"=>$arFields["ID"]),
			false,
			array("ID","IBLOCK_ID","ACTIVE","NAME","DESCRIPTION","IBLOCK_SECTION_ID","SECTION_PAGE_URL","DATE_CREATE","TIMESTAMP_X"),
			false)->GetNextElement();

		$arSection = $arSection->GetFields();

		$link = $_SERVER["SERVER_NAME"].$arSection["SECTION_PAGE_URL"];
		
		//get preview text
		$prev_text = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('IBLOCK_'.$arSection['IBLOCK_ID'].'_SECTION', $arFields["ID"], "UF_SHORT_HTML_TEXT", LANGUAGE_ID); 
		$prev_text = $prev_text["UF_SHORT_HTML_TEXT"];
		
		//если прошло 10 часов после создания элемента
		if((time()-strtotime($arSection["DATE_CREATE"]))>36000 && ($arSection["TIMESTAMP_X"]!="" && (time()-strtotime($arSection["TIMESTAMP_X"]))>36000)){
			$text = '';
			
			//active
			if($arFields["ACTIVE"]!=$arSection["ACTIVE"]){
				switch($arFields["ACTIVE"]){
					case "Y":
						$text .= "<h5>Раздел:</h5> Активирован<br />";
						break;
					case "N":
						$text .= "<h5>Раздел:</h5> Деактивирован<br />";
						break;
				}
			}
			
			if(strlen(trim(strip_tags($arFields["UF_SHORT_HTML_TEXT"])))!=strlen(trim(strip_tags($prev_text["VALUE"])))){
				$text .="<hr />";
				$text .= "<h5>Старый текст анонса:</h5>";
				$text .= $prev_text["VALUE"];
				$text .= "<h5>Новый текст анонса:</h5>";
				$text .= $arFields["UF_SHORT_HTML_TEXT"];
			}
			
			if(strlen(trim(strip_tags($arFields["DESCRIPTION"])))!=strlen(trim(strip_tags($arSection["DESCRIPTION"]))) /* || $arFields["DESCRIPTION_TYPE"]!=$arSection["DESCRIPTION_TYPE"]*/){
				$text .= "<br />";
				$text .="<hr />";
				$text .= "<h5>Старый текст описания:</h5>";
				$text .= $arSection["DESCRIPTION"];
				$text .= "<h5>Новый текст описания:</h5>";
				$text .= $arFields["DESCRIPTION"];
			} 
			
			if($arFields["IBLOCK_SECTION_ID"]!=$arSection["IBLOCK_SECTION_ID"]){
				$prent_sections[] = $arFields["IBLOCK_SECTION_ID"];
				$prent_sections[] = $arSection["IBLOCK_SECTION_ID"];
				$res = CIBlockSection::GetList(
					array(),
					array("ID"=>$prent_sections),
					false,
					array("ID","IBLOCK_ID","NAME")
					);
					
				while($arFields_sec = $res->GetNext()){
					$all_sec_name[$arFields_sec["ID"]]["NAME"] = $arFields_sec["NAME"];
				}
				
				$text .= "<h5>Старый родительский раздел:</h5> <br />";
				$text .= $all_sec_name[$arSection["IBLOCK_SECTION_ID"]]["NAME"]."<br />";
				
				$text .= "<h5>Новый родительский раздел:</h5> <br />";
				$text .= $all_sec_name[$arFields["IBLOCK_SECTION_ID"]]["NAME"]."<br />";
			}
			
			
			if($text!=""){
				$arFieldsMail = array(
					"EMAIL" => $email,
					"LINK" => $link,
					"NAME" => $arFields["NAME"],
					"LOGIN_CHANGE" => $USER->GetLogin(),
					"DATE_CHANGE" => date("d.m.Y H:i:s"),
					"TEXT" => $text,
				); 
			
				CEvent::Send(1871,'s1',$arFieldsMail,"N",69);
			}
		}
	}	
}

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "ChangeSectionMinPrice");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ChangeSectionMinPrice");

function ChangeSectionMinPrice($arFields){
	if($arFields["ID"]>0 && $arFields["IBLOCK_ID"]==21){
		$arElement = CIBlockElement::GetList(array(),array("ID"=>$arFields["ID"],"ACTIVE"=>"Y"),false,false,array("ID","PROPERTY_PRICE_FROM","IBLOCK_SECTION_ID"))->Fetch();
		if($arElement["IBLOCK_SECTION_ID"]>0){
			$resMinPrice = CIBlockElement::GetList(array("property_PRICE_FROM"=>"asc,nulls"),array("SECTION_ID"=>$arElement["IBLOCK_SECTION_ID"]),false,array("nTopCount"=>1),array("ID","IBLOCK_ID","PROPERTY_PRICE_FROM"))->Fetch();
			
			if($resMinPrice["PROPERTY_PRICE_FROM_VALUE"]!=""){
				$minPrice = str_replace(" ","",$resMinPrice["PROPERTY_PRICE_FROM_VALUE"]);
				$bs = new CIBlockSection;
				if($minPrice>0){
					$bs->Update($arElement["IBLOCK_SECTION_ID"],array("UF_MIN_PRICE"=>$minPrice));
				}
			}
		}
	}
}

AddEventHandler('main', 'OnBeforeEventSend', Array("MyForm", "my_OnBeforeEventSend"));
class MyForm
{
   function my_OnBeforeEventSend(&$arFields, &$arTemplate)
   {
	    global $APPLICATION;
		$dir = $APPLICATION->GetCurDir();
	    $arTemplate["SUBJECT"] = str_replace("#PAGE_CURENT#", $dir, $arTemplate["SUBJECT"]);
   }
}

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/validators/email_validator.php");

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "saleEmailSend");
function saleEmailSend($arFields){
    
    if($arFields["IBLOCK_ID"]==58 && $arFields["IBLOCK_SECTION"][0]==129){
        
        CModule::IncludeModule("iblock");
        $arSelect = Array("ID","IBLOCK_ID","NAME","DETAIL_TEXT");
        $arFilter = Array(
            "IBLOCK_ID"=>$arFields["IBLOCK_ID"],
            "PROPERTY_SUBS_FILES"=>$arFields["ID"],
            "ACTIVE"=>"Y",
            "SECTION_ID"=>130, 
            "PROPERTY_HOW_SEND"=>64,
           /* ">=TIMESTAMP_X" => ConvertTimeStamp(time()-100, "FULL"),
            "<=TIMESTAMP_X"   => ConvertTimeStamp(time(), "FULL")*/
        );
        
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement())
        {
            $fields = $ob->GetFields();
            $properties = $ob->GetProperties();
            
            $text='<table cellpadding="0" cellspacing="0" width="100%">
            <tr>
            <td>
                <table align="center" cellpadding="0" cellspacing="0" width="600" style="font-family: Tahoma, Verdana, Arial sans-serif;">
                    <tr>
                        <td>';
            $text=$text.'<table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="font-family: Tahoma, Verdana, Arial sans-serif; font-size: 18px;color: #555555;">
                        <p>Добрый день!</p>
                        <p>Представляем Вам обновленную информацию о товарах раздела «Распродажа». </br>';
            
            
            $text = $text.$arFields["NAME"].' — обновлено '.date("d.m.Y").'</p>';
            $text=$text.'</td></tr><tr><td height="20"></td></tr></table>';
            $text=$text.'<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px;">
                <thead>
                    <tr>
                        <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center; border-right: 1px solid #fff;">
                            Файл
                        </td>
                        <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center; border-right: 1px solid #fff">
                            Наименования
                        </td>
                        <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center;">
                            Позвоните или напишите нам
                        </td>
                    </tr>
                </thead>
                <tbody valign="middle">';
            
                $db_props = CIBlockElement::GetProperty(58, $arFields["ID"], array("sort" => "asc"), Array("CODE"=>"FILE"));
                if($ar_props = $db_props->Fetch())
                $text = $text.'<tr valign="middle">
                    <td valign="middle" style="width: 20%; text-align: center; padding: 25px 10px; border-right: 1px solid #ff5000; border-bottom: 1px solid #ff5000;">
                        <a href="'.CFile::GetPath($ar_props["VALUE"]).'">
                            <img src="/upload/images/icon.jpg" alt="">

                        </a>
                    </td>
                    <td valign="middle" style="color: black; font-weight: bold; font-size: 12px; width: 40%; padding: 25px 10px; border-right: 1px solid #ff5000; border-bottom: 1px solid #ff5000">
                        <p style="color: black; display: inline-block; font-weight: bold; font-size: 12px;">'.$arFields["NAME"].'</p>
                    </td>
                        <td style="width: 40%; padding: 25px 10px; border-bottom: 1px solid #ff5000;">'.$arFields["DETAIL_TEXT"].'
                    </td>
                  </tr>';
            
                $text=$text.'</tbody>
                    </table>
					<!--<p style="text-align:center; color: black;font-size: 12px;">Это письмо сгенерировано автоматически, пожалуйста, не отвечайте на него</p>-->
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="center" style="padding-top: 40px;">
                                            <a style="font-size: 12px; color: #555555; text-decoration: none;" href="http://profholod.ru/substandard/?action=unsubscribe&email='.$fields["NAME"].'">| Отписаться от рассылки |</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </table>';

            $arEventFields=array(
                "USER"=>$properties["SUBS_NAME"]["VALUE"],
                "EMAIL_TO"=>$fields["NAME"],
                "DETAIL_TEXT"=>$text
            );
            
            CEvent::Send("SALE_SUBSCRIBE", 's1', $arEventFields, "N", 77);
            
        }
        
    }
}

function saleEmailSendAgent(){
        CModule::IncludeModule("iblock");
        $arSelect = Array("ID","IBLOCK_ID","NAME","TIMESTAMP_X","DETAIL_TEXT","PROPERTY_FILE");
        $arFilter = Array(
            "IBLOCK_ID"=>58,
            "ACTIVE"=>"Y",
            "SECTION_ID"=>129, 
            ">=TIMESTAMP_X" => ConvertTimeStamp(time()-(86400*7), "FULL"),
            "<=TIMESTAMP_X"   => ConvertTimeStamp(time(), "FULL")
        );
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNext())
        {
            $filterID[$ob["ID"]]=$ob["ID"]; 
            $info[$ob["ID"]]["NAME"]=$ob["NAME"];
            $info[$ob["ID"]]["TIMESTAMP_X"]=$ob["TIMESTAMP_X"];
            $info[$ob["ID"]]["DETAIL_TEXT"]=$ob["DETAIL_TEXT"];
            $info[$ob["ID"]]["FILE_PATH"]=CFile::GetPath($ob["PROPERTY_FILE_VALUE"]);
        }
    
        if(!empty($filterID)){
            $arSelect = Array("ID","IBLOCK_ID","NAME");
            $arFilter = Array(
                "IBLOCK_ID"=>58,
                "ACTIVE"=>"Y",
                "PROPERTY_SUBS_FILES"=>$filterID,
                "SECTION_ID"=>130, 
                "PROPERTY_HOW_SEND"=>65
            );
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            while($ob = $res->GetNextElement())
            {
                $fields = $ob->GetFields();
                $properties = $ob->GetProperties();
                $userInfo[$fields["ID"]]["EMAIL"]=$fields["NAME"];
                $userInfo[$fields["ID"]]["USER"]=$properties["SUBS_NAME"]["VALUE"];
                $userInfo[$fields["ID"]]["SUBS_FILES"]=$properties["SUBS_FILES"]["VALUE"];
            }
            foreach($userInfo as $user){
                    unset($text);
                    $text='<table cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>
                                    <table align="center" cellpadding="0" cellspacing="0" width="600" style="font-family: Tahoma, Verdana, Arial sans-serif;">
                                        <tr>
                                            <td>';
                    $text=$text.'<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="font-family: Tahoma, Verdana, Arial sans-serif; font-size: 18px;color: #555555;">
											<p>Добрый день!</p>
											<p>Представляем Вам обновленную информацию о товарах раздела «Распродажа». </br>';
                
                            foreach($user["SUBS_FILES"] as $file){
                                if($filterID[$file]){
                                    $text = $text.$info[$file]["NAME"].' — обновлено '.$info[$file]["TIMESTAMP_X"].'</br>';
                                }
                        }
                    $text=$text.'</p></td></tr><tr><td height="20"></td></tr></table>';
                
                
                    if(!empty($user["SUBS_FILES"])){
                        
                    $text=$text.'<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px;">
									<thead>
                                        <tr>
                                            <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center; border-right: 1px solid #fff;">
                                                Файл
                                            </td>
                                            <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center; border-right: 1px solid #fff">
                                                Наименования
                                            </td>
                                            <td style="background: #ff5000; color: #fff; padding: 10px 0; text-align: center;">
                                                Позвоните или напишите нам
                                            </td>
                                        </tr>
									</thead>
                                    <tbody valign="middle">';
                        
                        foreach($user["SUBS_FILES"] as $file){
                            if($filterID[$file]){
                                $text = $text.'<tr valign="middle">
                                                <td valign="middle" style="width: 20%; text-align: center; padding: 25px 10px; border-right: 1px solid #ff5000; border-bottom: 1px solid #ff5000;">
                                                    <a href="'.$info[$file]["FILE_PATH"].'">
                                                        <img src="/upload/images/icon.jpg" alt="">
                                                        
                                                    </a>
                                                </td>
                                                <td valign="middle" style="color: black; font-weight: bold; font-size: 12px; width: 40%; padding: 25px 10px; border-right: 1px solid #ff5000; border-bottom: 1px solid #ff5000">
                                                    <p style="color: black; display: inline-block; font-weight: bold; font-size: 12px;">'.$info[$file]["NAME"].'</p>
                                                </td>
                                                    <td style="width: 40%; padding: 25px 10px; border-bottom: 1px solid #ff5000;">'.$info[$file]["DETAIL_TEXT"].'
                                                </td>
										      </tr>';
                            }
                        }
                        
                        $text=$text.'</tbody>
                                        </table>
										<!--<p style="text-align:center; color: black;font-size: 12px;">Это письмо сгенерировано автоматически, пожалуйста, не отвечайте на него</p>-->
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="padding-top: 40px;">
                                                                <a style="font-size: 12px; color: #555555; text-decoration: none;" href="http://profholod.ru/substandard/?action=unsubscribe&email='.$user["EMAIL"].'">| Отписаться от рассылки |</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                </table>';
                     }
                    $arEventFields=array(
                        "EMAIL_TO"=>$user["EMAIL"],
                        "DETAIL_TEXT"=>$text
                    );
                if(!empty($user["SUBS_FILES"])){
                    CEvent::Send("SALE_SUBSCRIBE", 's1', $arEventFields, "N", 77);
                }
            }               
        }
    
    return "saleEmailSendAgent();";
}


if(!class_exists('WsSecure')){
	AddEventHandler("main", "OnAfterUserAuthorize", Array("WsSecure", "checkAdminAuth"));
	
	class WsSecure{
		
		function checkAdminAuth($arFields) {
			global $USER, $USER_CHECK;
			if($_SERVER['SCRIPT_NAME'] !='/bitrix/tools/public_session.php') {
				if($USER_CHECK !== false && $USER->isAdmin()){
					$USER_CHECK = false;
					$need_logout = true;
					$cookie_login = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"];
					$cookie_md5pass = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_UIDH"];
					
					if(!empty($cookie_login) && !empty($cookie_md5pass)) {
						$arAuthHashResult = $USER->LoginByHash($cookie_login, $cookie_md5pass);
						if($arAuthHashResult === true){
							$need_logout = false;
						}
					}
					
					$login = $_REQUEST['USER_LOGIN'];
					$password = $_REQUEST['USER_PASSWORD'];
					
					if(isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER'])){
						$login = $_SERVER['PHP_AUTH_USER'];
					}
					
					if(isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])){
						$password = $_SERVER['PHP_AUTH_PW'];
					}
					
					if(!empty($login) && !empty($password)) {
						$arAuthResult = $USER->Login($login, $password);
						if($arAuthResult === true){
							$need_logout = false;
						}
					}
					
					if($need_logout) {
						$server_data=$_SERVER;
						unset($server_data['PHP_AUTH_PW']);
						ob_start();
						echo "<pre>";
						print_r($server_data);
						echo "</pre>";
						$wrong_auth = ob_get_clean();
						
						bxmail('bojkoio@gmail.com', 'Wrong auth', $wrong_auth);
						bxmail('monkofpain@gmail.com', 'Wrong auth', $wrong_auth);
						
						$USER->Logout();
					}
				}
			}
		}
		
	}
}

// custom_mail for subscribe

function custom_mail($to, $subject="", $message="", $additional_headers="", $additional_parameters="")
{
	$eol = \Bitrix\Main\Mail\Mail::getMailEol();
	$additional_headers = $additional_headers.$eol.'Reply-to: sales@profholod.ru';
	
	if(strpos($to, 'lena@wise-solutions.com.ua') !== false || strpos($to, 'profholodIn@yandex.ru') !== false || strpos($to, 'profholod@wise-solutions.com.ua') !== false)	
		return false;
		
	if($additional_parameters!="")
        return mail($to, $subject, $message, $additional_headers, $additional_parameters);

    return mail($to, $subject, $message, $additional_headers);
}

AddEventHandler("subscribe", "BeforePostingSendMail", Array("MyClass", "BeforePostingSendMailHandler"));

class MyClass
{
    // создаем обработчик события "BeforePostingSendMail"
    function BeforePostingSendMailHandler($arFields)
    {	
        $arFields["BODY"] = str_replace("#EMAIL#", $arFields["EMAIL"],  $arFields["BODY"]);
		$arFields["BODY"] = str_replace("#EMAIL_HASH#", EmailHash($arFields["EMAIL"]),  $arFields["BODY"]); // для ссилки на отписку
        return $arFields;
    }
}

require_once('include/tcpdf/tcpdf.php');	

//генерація пдф файлу
function CreatePdfFile($html, $page_format = true, $name = 'calculator.pdf', $doc_type = 'D'){

	if(empty($html)){
		return false;
	}
	$html = iconv("cp1251","utf-8", $html);
	global $APPLICATION;
	$APPLICATION->RestartBuffer();
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);


	// set default header data
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	// ---------------------------------------------------------

	// set font
	$pdf->SetFont('tahoma', '', 12);

	// add a page
	if($page_format){
		$pdf->AddPage('L' , 'A4');
	}else{
		$pdf->AddPage('A4');
	}

	// output the HTML content
	$pdf->writeHTML($html, true, false, true, false, '');


	// reset pointer to the last page
	$pdf->lastPage();
	
	// ---------------------------------------------------------
	//Close and output PDF document
	$pdf->Output($name, $doc_type);	
}


function sections_sort($a, $b) {
	if ($a["SORT"] == $b["SORT"]) {
		return 0;
	}
	return ($a["SORT"] < $b["SORT"]) ? -1 : 1;
}

function sections_sort_main($a, $b) {
	if ($a["UF_SORT_MAIN"] == $b["UF_SORT_MAIN"]) {
		return 0;
	}
	return ($a["UF_SORT_MAIN"] < $b["UF_SORT_MAIN"]) ? -1 : 1;
}

// Хеширование Email
function EmailHash($var) {
    $salt1 = 'ABCDE';
    $salt2 = 'FGHIJ';
    $var = crypt(md5($var.$salt1),$salt2);
    return $var;
}
?>