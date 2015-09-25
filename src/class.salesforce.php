<?php

	class salesforce
	{
		// populating error properties
		public $errors = array();
		public $errorCount = 0;

		public static $httpErrors = Array(
			"400 Bad Request" => "The request could not be understood by the Salesforce due to malformed syntax.",
			"401 Unauthorized" => "Salesforce authentication failed.",
			"403 Forbidden" => "Salesforce understood the request, but is refusing to fulfill it.",
			"404 Not Found" => "The report or page specified could not be found.",
			"405 Method Not Allowed" => "Salesforce understood the request, but cannot fulfill it.",
			"408 Request Timeout" => "Salesforce did not respond within the time that Greyskull was prepaired to wait.",
			"500 Internal Server Error" => "Salesforce encountered an unexpected condition which prevented it from fulfilling the request.",
			"502 Bad Gateway" => "",
			"504 Gateway Timeout" => ""
		);

		// Constructor logs into salesforce
		function __construct($username = "default@default.com", $password = "default")
		{
			$loginURL = 'https://login.salesforce.com/';
			$this->partition = "na12";

			// Setting up POST data
			$postFeilds = array(
				'username' => $username,
				'un' => $username,
				'pw' => $password,
				'useSecure' => true,
				'it' => 'standard'
			);

			// setting up cURL and designating the cookie file
			$this -> curl = curl_init();

			// Feeding the cURL object
			curl_setopt($this -> curl, CURLOPT_URL, $loginURL);
			curl_setopt($this -> curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($this -> curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($this -> curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6");
			curl_setopt($this -> curl, CURLOPT_HEADER, 30);
			curl_setopt($this -> curl, CURLOPT_POST, true);
			curl_setopt($this -> curl, CURLOPT_POSTFIELDS, $postFeilds);
			curl_setopt($this -> curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this -> curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this -> curl, CURLOPT_COOKIESESSION, true);
			curl_setopt($this -> curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($this -> curl, CURLOPT_TIMEOUT, 300);

			// Fire cURL and report any errors
			$answer = curl_exec($this -> curl);
			if (curl_error($this -> curl))
			{
				$this -> errorCount++;
				$this -> errors[] = curl_error($this -> curl);
			}
			$this -> captureCookie($answer);

			// Get the login result, Salesforce will redirect on sucess
			$status = curl_getinfo($this -> curl);
			$this -> loginResult = $status['url'] !== 'https://login.salesforce.com/';

			if (!$this -> loginResult)
			{
				$this -> errorCount++;
				$this -> errors[] = 'Failed to login to Salesforce, please verify your login information.';
			}

		}

		function report($reportId)
		{
			if ($this -> loginResult)
			{
				// Construct the url
				$partition = $this->partition
				$url[0] = "https://$partition.salesforce.com/";
				$url[1] = trim($reportId);
				$url[2] = '?export=1&enc=UTF-8&xf=csv';

				$url = implode($url);

				// Feed the cURL object some more
				curl_setopt($this -> curl, CURLOPT_URL, $url);
				curl_setopt($this -> curl, CURLOPT_POST, false);
				curl_setopt($this -> curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($this -> curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($this -> curl, CURLOPT_COOKIE, $this -> cookies);

				// Fire cURL, report errors
				$answer = curl_exec($this -> curl);
				if (curl_error($this -> curl))
				{
					$this -> errorCount++;
					$this -> errors[] = curl_error($this -> curl);
				}

				if (strpos($answer, 'Content-Type: text/csv') !== false)
				{
					// Remove the header info
					$csv = preg_replace('/HTTP[\s\S]*chunked/', '', $answer);

					// Break the string into rows
					$rows = explode("\n", $csv);

					if (is_array($rows))
					{
						foreach ($rows as $i => $row)
						{
							$row = trim($row);

							if (strlen($row) > 0)
							{
								$array[] = str_getcsv($row);
							}
						}

						if (is_array($array))
						{
							$colums = $array[0];

							// $result[] = $colums;

							foreach ($array as $i => $row)
							{
								$hasCols = array_keys($colums) === array_keys($row);

								if ($hasCols)
								{
									foreach ($row as $col => $val)
									{
										if ($i == 0)
										{
											$result[$i][$col] = trim($val);
										}
										else
										{
											$result[$i][$col] = trim($val);
										}
									}
								}
							}
						}
					}

					return $result;
				}
				else
				{
					$this -> errorCount++;
					$this -> errors[] = 'Failed to aquire report ' . $reportId;
				}

			}
			else
			{
				$this -> errorCount++;
				$this -> errors[] = 'Failed to login to Salesforce, please verify your login information.';
			}
		}

		public function reportDetailed($reportId, $searchParam = FALSE)
		{
			if ($this -> loginResult)
			{
				// Construct the url
				$partition = $this->partition
				$urlPart[0] = "https://$partition.salesforce.com/";
				$urlPart[1] = trim($reportId);
				$urlPart[2] = '?export=1&enc=UTF-8&xf=csv';
				if (is_array($searchParam))
				{
					foreach ($searchParam as $i => $val)
					{
						$prams[] = "&$i=$val";
					}

					$urlPart[3] = implode('', $prams);
				}
				else
				{
					$urlPart[3] = $searchParam == FALSE ? '' : '&pv0=' . trim($searchParam);
				}

				$url = implode($urlPart);
				//echo $url.'<br><br>';

				$this -> urlString = $url;

				// Feed the cURL object some more
				curl_setopt($this -> curl, CURLOPT_URL, $url);
				curl_setopt($this -> curl, CURLOPT_POST, false);
				curl_setopt($this -> curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($this -> curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($this -> curl, CURLOPT_COOKIE, $this -> cookies);

				// Fire cURL, report errors
				$answer = curl_exec($this -> curl);
				if (curl_error($this -> curl))
				{
					$this -> errorCount++;
					$this -> errors[] = curl_error($this -> curl);
				}

				if (strpos($answer, 'Content-Type: text/csv') !== false)
				{
					// Remove the header info
					$csv = preg_replace('/HTTP[\s\S]*chunked/', '', $answer);

					// Break the string into rows
					$rows = explode("\n", $csv);
					$this -> testParse = $this -> parse($csv, ',', true);

					if (is_array($rows))
					{
						foreach ($rows as $i => $row)
						{
							$row = trim($row);

							if (strlen($row) > 0)
							{
								$array[] = str_getcsv($row);
							}
						}

						if (is_array($array))
						{

							// Lot's of looping to get the report in the right format

							$colums = $array[0];

							foreach ($array as $i => $row)
							{
								$hasCols = array_keys($colums) === array_keys($row);

								if ($hasCols)
								{
									foreach ($row as $col => $val)
									{
										if ($i == 0)
										{
											$result[$i][$col] = trim($val);
										}
										else
										{
											$result[$i][$col] = trim($val);
										}
									}
								}
							}
						}
					}

					return $result;
				}
				else
				{
					$this -> errorCount++;
					$this -> errors[] = 'Failed to aquire report ' . $reportId;

					$this -> errors[] = 'Invalid response: ' . $answer;
				}

			}
			else
			{
				$this -> errorCount++;
				$this -> errors[] = 'Failed to login to Salesforce, please verify your login information.';
			}
		}

		// Accepts a cURL response that contains the header and stores the session
		// cookies in $this->cookies.
		private function captureCookie($answer)
		{
			$headerSize = curl_getinfo($this -> curl, CURLINFO_HEADER_SIZE);
			$header = substr($answer, 0, $headerSize);
			preg_match_all("/^Set-cookie: (.*?);/ism", $header, $cookieCrumbs);

			if (is_array($cookieCrumbs[1]))
			{
				$this -> cookies = implode(';', $cookieCrumbs[1]);
			}
		}

		public function __deconstructor()
		{
			$partition = $this->partition	
			$url = 'https://$partition.salesforce.com/secur/logout.jsp';
			curl_setopt($this -> curl, CURLOPT_URL, $url);
			curl_setopt($this -> curl, CURLOPT_FOLLOWLOCATION, true);
			$answer = curl_exec($this -> curl);

			curl_close($this -> curl);
		}

		private function parse($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
		{
			$enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
			$enc = preg_replace_callback('/"(.*?)"/s', function($field)
			{
				return urlencode(utf8_encode($field[1]));
			}, $enc);
			$lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
			return array_map(function($line) use ($delimiter, $trim_fields)
			{
				$fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
				return array_map(function($field)
				{
					return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
				}, $fields);
			}, $lines);
		}

	}
?>