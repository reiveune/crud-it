<?php
//é
	#a session variable is set by class for "C"reate functionality -- eg adding a row
    session_start();

    #for pesky IIS configurations without silly notifications turned off
    error_reporting(E_ALL - E_NOTICE);

	#this is the link to your database -- it is the only requirement for the class: an active db connection
    ####################################################################################
    ##
    require_once("database.php");
    ##
    ####################################################################################
	
	//function unicode_urldecode
	//author : Stéphane Delaune
	function unicode_urldecode($url)
	{
		preg_match_all('/%u([[:alnum:]]{4})/', $url, $a);
	   
		foreach ($a[1] as $uniord)
		{
			$dec = hexdec($uniord);
			$utf = '';
		   
			if ($dec < 128)
			{
				$utf = chr($dec);
			}
			else if ($dec < 2048)
			{
				$utf = chr(192 + (($dec - ($dec % 64)) / 64));
				$utf .= chr(128 + ($dec % 64));
			}
			else
			{
				$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
				$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
				$utf .= chr(128 + ($dec % 64));
			}
		   
			$url = str_replace('%u'.$uniord, $utf, $url);
		}
	   
		return urldecode($url);
	}
	
	//function verifdata
	//author : Stéphane Delaune
	function verifdata($fieldcontent, $type, $emptyallow)
	{
		if($emptyallow==true and $fieldcontent=='')
		{
			return true;
		}
		else if($emptyallow==false and $fieldcontent=='')
		{
			return false;
		}
		else
		{
			switch ($type)
			{
				case "isEmail":
					if(filter_var($fieldcontent, FILTER_VALIDATE_EMAIL))
						return true;
		  			else
						return false;
				break;
				case "isURL":
					if(filter_var($fieldcontent, FILTER_VALIDATE_URL))
		     			return true;
		  			else
						return false;
				break;
				case "isBool":
					if(filter_var($fieldcontent, FILTER_VALIDATE_BOOLEAN))
		     			return true;
		  			else
		    			return false;
				break;
				case "isFloat":
					if(filter_var($fieldcontent, FILTER_VALIDATE_FLOAT))
		     			return true;
		  			else
		    			return false;
				break;
				case "isInteger":
					if(filter_var($fieldcontent, FILTER_VALIDATE_INT))
						return true;
					else
						return false;
				break;
				case "isIP":
					if(filter_var($fieldcontent, FILTER_VALIDATE_IP))
						return true;
					else
						return false;
				break;
				case "isRegex":
					if(filter_var($fieldcontent, FILTER_VALIDATE_REGEXP))
						return true;
					else
						return false;
				break;
				case "is0or1":
					if($fieldcontent==0 || $fieldcontent==1)
		     			return true;
		  			else
		    			return false;
				break;
				case "isDate":
					$Syntaxe='#^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						return true;
					else
						return false;
				break;
				case "isTel":
					$Syntaxe='#^[0-9]{3,20}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						return true;
					else
						return false;
				break;
				case "isTelbis":
					$Syntaxe='#^[0-9]{10,20}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						return true;
					else
						return false;
				break;
				case "isWord":
					mb_regex_encoding("UTF-8");
					$fieldcontent = mb_strtolower($fieldcontent, 'UTF-8');
					$fieldcontent = str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ'), array('a', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'e', 'e', 'c', 'y', 'n'), $fieldcontent);
					$Syntaxe='#^([[:alnum:]]*[[:space:]]*[.:,;!?\'\-\(\)/]*)*+$#';
					$Syntaxe2='#^[[:space:]]*+$#';
					if(preg_match($Syntaxe,$fieldcontent) && !preg_match($Syntaxe2,$fieldcontent))
					{
						return true;
					}
					else
					{
						return false;
					}
				break;
				case "isPhrase":
					mb_regex_encoding("UTF-8");
					$fieldcontent = mb_strtolower($fieldcontent, 'UTF-8');
					$fieldcontent = str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ'), array('a', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'e', 'e', 'c', 'y', 'n'), $fieldcontent);
					$Syntaxe='#^([[:alnum:]]*[[:space:]]*[.:,;!?\'\-\(\)/]*)*+$#';
					$Syntaxe2='#^[[:space:]]*+$#';
					if(preg_match($Syntaxe,$fieldcontent) && !preg_match($Syntaxe2,$fieldcontent))
					{
						return true;
					}
					else
					{
						return false;
					}
				break;

				case "isAdresse":
					mb_regex_encoding("UTF-8");
					$fieldcontent = mb_strtolower($fieldcontent, 'UTF-8');
					$fieldcontent = str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ'), array('a', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'e', 'e', 'c', 'y', 'n'), $fieldcontent);
					$Syntaxe='#^([[:alnum:]]*[[:space:]]*[.:,;!?\'\-\(\)/]*)*+$#';
					$Syntaxe2='#^[[:space:]]*+$#';
					if(preg_match($Syntaxe,$fieldcontent) && !preg_match($Syntaxe2,$fieldcontent))
					{
						return true;
					}
					else
					{
						return false;
					}
				break;
				case "isCivilite":
					if(($fieldcontent=="M." || $fieldcontent=="Mme" || $fieldcontent=="Mlle"))
						return true;
					else
						return false;
				break;
				case "isCP":
					$Syntaxe='#^[0-9]{3,8}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						return true;
					else
						return false;
				break;
				case "isDay":
					$Syntaxe='#^[0-9]{1,2}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						if($fieldcontent <= 31)
							return true;
						else
							return false;
					else
						return false;
				break;
				case "isMonth":
					$Syntaxe='#^[0-9]{1,2}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						if($fieldcontent <= 12)
							return true;
						else
							return false;
					else
						return false;
				break;
				case "isYear":
					$Syntaxe='#^[0-9]{4}$#';
					if(preg_match($Syntaxe,$fieldcontent))
						return true;
					else
						return false;
				break;
				default:
					return true;
				break;
			}
		}
	}
	

?>