<?
class CSiteMapCustom extends CSiteMap
{
	function Create($site_id, $max_execution_time, $NS, $arOptions = array())
	{
		@set_time_limit(0);
		if(!is_array($NS))
		{
			$NS = Array(
				"ID"=>0,
				"CNT"=>0,
				"FILE_SIZE"=>0,
				"FILE_ID"=>1,
				"FILE_URL_CNT"=>0,
				"ERROR_CNT"=>0,
				"PARAM2"=>0,
			);
		}
		else
		{
			$NS = Array(
				"ID"=>intval($NS["ID"]),
				"CNT"=>intval($NS["CNT"]),
				"FILE_SIZE"=>intval($NS["FILE_SIZE"]),
				"FILE_ID"=>intval($NS["FILE_ID"]),
				"FILE_URL_CNT"=>intval($NS["FILE_URL_CNT"]),
				"ERROR_CNT"=>intval($NS["ERROR_CNT"]),
				"PARAM2"=>intval($NS["ID"]),
			);
		}

		if(is_array($max_execution_time))
		{
			$record_limit = $max_execution_time[1];
			$max_execution_time = $max_execution_time[0];
		}
		else
		{
			$record_limit = 5000;
		}

		if($max_execution_time > 0)
		{
			$end_of_execution = time() + $max_execution_time;
		}
		else
		{
			$end_of_execution = 0;
		}

		if(is_array($arOptions) && ($arOptions["FORUM_TOPICS_ONLY"] == "Y"))
			$bForumTopicsOnly = CModule::IncludeModule("forum");
		else
			$bForumTopicsOnly = false;

		if(is_array($arOptions) && ($arOptions["BLOG_NO_COMMENTS"] == "Y"))
			$bBlogNoComments = CModule::IncludeModule("blog");
		else
			$bBlogNoComments = false;

		if(is_array($arOptions) && ($arOptions["USE_HTTPS"] == "Y"))
			$strProto = "https://";
		else
			$strProto = "http://";

		$rsSite=CSite::GetByID($site_id);
		if($arSite=$rsSite->Fetch())
		{
			$SERVER_NAME = trim($arSite["SERVER_NAME"]);
			if(strlen($SERVER_NAME) <= 0)
			{
				$this->m_error=GetMessage("SEARCH_ERROR_SERVER_NAME", array("#SITE_ID#" => '<a href="site_edit.php?LID='.urlencode($site_id).'&lang='.urlencode(LANGUAGE_ID).'">'.htmlspecialchars($site_id).'</a>'))."<br>";
				return false;
			}
			//Cache events
			$events = GetModuleEvents("search", "OnSearchGetURL");
				while ($arEvent = $events->Fetch())
					$this->m_events[]=$arEvent;
			//Clear error file
			if($NS["ID"]==0 && $NS["CNT"]==0)
			{
				$e=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml", "w");
				$strBegin="<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
				fwrite($e, $strBegin);
			}
			//Or open it for append
			else
			{
				$e=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml", "a");
			}
			if(!$e)
			{
				$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_errors.xml"."<br>";
				return false;
			}
			//Open current sitemap file
			if($NS["FILE_SIZE"]==0)
			{
				$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml", "w");
				$strBegin="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
				fwrite($f, $strBegin);
				$NS["FILE_SIZE"]+=strlen($strBegin);

			}
			else
			{
				$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml", "a");
			}
			if(!$f)
			{
				$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap_".sprintf("%03d",$NS["FILE_ID"]).".xml"."<br>";
				return false;
			}

			CTimeZone::Disable();
			$this->GetURLs($site_id, $NS["ID"], $record_limit);
			$bFileIsFull=false;
			
			// INDEX PAGE HERE
			$strURL = $this->LocationEncode($strProto.$ar["SERVER_NAME"].$this->URLEncode($SERVER_NAME . '/', "UTF-8"));
			$strTime = $this->TimeEncode(time());

			$strToWrite = "\t<url>\n\t\t<loc>".$strURL."</loc>\n\t\t<lastmod>".$strTime."</lastmod>"
						. "\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>1</priority>\n\t</url>\n";



			if(strlen($strURL) > 2048) {
				fwrite($e, $strToWrite);
				$NS["ERROR_CNT"]++;
			} else {
				fwrite($f, $strToWrite);
				$NS["CNT"]++;
				$NS["FILE_SIZE"] += strlen($strToWrite);
				$NS["FILE_URL_CNT"]++;
			}
			
			// iterators
			while (!$bFileIsFull && $ar = $this->Fetch())
			{
				$record_limit--;
				$NS["ID"] = $ar["ID"];
				
				$bIgnore = false;
				
				$ar["URL"] = trim($ar["URL"]);
				$ar["URL"] = urldecode($ar["URL"]);
				
				/*
				 *
				 * маски с игнорируемыми путями
				 *
				 */
				$arIgnoreRegS = array(
					'#/login/#is',
					'#/404_inc.php#',
					'#/users/#is',
					'#/cat/_tests/#is',
					'#/cat/(.*?)\.php$#',
					'#/subscription/#',
					'#/finance/#',
				);
				foreach ($arIgnoreRegS as $reg) {
					if (preg_match($reg, $ar["URL"])) {
						$bIgnore = true;
						break;
					}
				}
				
				if ($ar["URL"] == '' || $bIgnore) {
					continue;
				}

				if($bForumTopicsOnly && ($ar["MODULE_ID"] == "forum"))
				{
					//Forum topic ID
					$PARAM2 = intval($ar["PARAM2"]);
					if($NS["PARAM2"] < $PARAM2)
					{
						$NS["PARAM2"] = $PARAM2;
						$arTopic = CForumTopic::GetByIDEx($PARAM2);
						if($arTopic)
							$ar["FULL_DATE_CHANGE"] = $arTopic["LAST_POST_DATE"];
					}
					else
					{
						continue;
					}
				}

				if($bBlogNoComments && ($ar["MODULE_ID"] == "blog"))
				{
					if(substr($ar["ITEM_ID"], 0, 1) === "C")
						continue;
				}

				$strURL = $this->LocationEncode($strProto.$ar["SERVER_NAME"].$this->URLEncode($ar["URL"], "UTF-8"));
				$strTime = $this->TimeEncode(MakeTimeStamp(ConvertDateTime($ar["FULL_DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS"), "DD.MM.YYYY HH:MI:SS"));
				if (!in_array($strURL, $_SESSION['arResURLXMLMap'])) {
					$strToWrite  = "\t<url>\n\t\t<loc>".$strURL."</loc>\n\t\t<lastmod>".$strTime."</lastmod>"
						. "\n\t\t<changefreq>daily</changefreq>\n\t\t<priority>0.8</priority>\n\t</url>\n";
					$_SESSION['arResURLXMLMap'][] = $strURL;

					if(strlen($strURL) > 2048)
					{
						fwrite($e, $strToWrite);
						$NS["ERROR_CNT"]++;
					}
					else
					{
						fwrite($f, $strToWrite);
						$NS["CNT"]++;
						$NS["FILE_SIZE"]+=strlen($strToWrite);
						$NS["FILE_URL_CNT"]++;
					}
				}
				//Next File on file size or url count limit
				if($NS["FILE_SIZE"]>9000000 || $NS["FILE_URL_CNT"]>=50000)
				{
					$bFileIsFull=true;
				}
				elseif($end_of_execution)
				{
					if(time() > $end_of_execution)
					{
						fclose($e);
						fclose($f);
						CTimeZone::Enable();
						return $NS;
					}
				}
			}

			CTimeZone::Enable();

			if($bFileIsFull)
			{
				fwrite($e,"</urlset>\n");
				fclose($e);
				fwrite($f,"</urlset>\n");
				fclose($f);

				$NS["FILE_SIZE"]=0;
				$NS["FILE_URL_CNT"]=0;
				$NS["FILE_ID"]++;
				return $NS;
			}
			elseif($record_limit<=0)
			{
				return $NS;
			}
			else
			{
				fwrite($e,"</urlset>\n");
				fclose($e);
				fwrite($f,"</urlset>\n");
				fclose($f);
			}
			
			//WRITE INDEX FILE HERE
			$f=fopen($arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap.xml", "w");
			if(!$f)
			{
				$this->m_error=GetMessage("SEARCH_ERROR_OPEN_FILE")." ".$arSite["ABS_DOC_ROOT"].$arSite["DIR"]."sitemap.xml"."<br>";
				return false;
			}
			$strBegin="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
			fwrite($f, $strBegin);
			for($i = 0; $i <= $NS["FILE_ID"]; $i++)
			{
				$strFile = $arSite["DIR"]."sitemap_".sprintf("%03d",$i).".xml";
				$strTime = $this->TimeEncode(filemtime($arSite["ABS_DOC_ROOT"].$strFile));
				fwrite($f,
					"\t<sitemap>\n\t\t<loc>###".$strProto.$arSite["SERVER_NAME"].$strFile."</loc>\n\t\t<lastmod>".$strTime."</lastmod>\n\t</sitemap>\n"
				);

			}
			fwrite($f,"</sitemapindex>\n");
			fclose($f);
			$this->m_errors_count=$NS["ERROR_CNT"];
			$this->m_errors_href=$strProto.$arSite["SERVER_NAME"].$arSite["DIR"]."sitemap_errors.xml";
			$this->m_href = $strProto.$arSite["SERVER_NAME"].$arSite["DIR"]."sitemap.xml";
			return true;
		}
		else
		{
			$this->m_error=GetMessage("SEARCH_ERROR_SITE_ID")."<br>";
			return false;
		}
	}
}
?>
