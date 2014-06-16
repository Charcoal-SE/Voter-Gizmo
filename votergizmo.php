<?php

echo "Access token needed (to perform the voting). Please go to www.erwaysoftware.com/oauth to get an access token." . PHP_EOL . "Enter access token: ";

$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$access_token = trim($line);

echo "validating token..." . PHP_EOL;

$accesstokenvalidateurl = 'https://api.stackexchange.com/2.2/access-tokens/' . $access_token;
$accesstokenvalidatedata = array();
$response = (new Curl)->exec($accesstokenvalidateurl . '?' . http_build_query($accesstokenvalidatedata), [CURLOPT_ENCODING => 'gzip']);

$tokenvalidate=json_decode($response);

$colors = new Colors();

if (count($tokenvalidate->{"items"}) > 0)
{
	echo $colors->getColoredString("Your token looks good. Wonderful.", "green") . PHP_EOL . PHP_EOL;
}
else
{
	echo $colors->getColoredString("Your token couldn't be validated. You might not be able to retag questions.", "red") . PHP_EOL . PHP_EOL;
}

while (1)
{
	echo "Site to inspect? (api key): ";

	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);

	$site = trim($line);

	echo "site: " . $site . PHP_EOL;

	echo "fetching last 100 questions on " . $site . "...";

	$questionsurl = 'https://api.stackexchange.com/2.2/questions';
	$questionsdata = array('sort' => 'creation', "site" => $site, "tagged" => $tag, "key" => "6Z09liTt4uTQU*a4DYOXVQ((", "access_token" => $access_token, "filter" => "!)riR77pOspGbk)Ji)Mdz");
	$response = (new Curl)->exec($questionsurl . '?' . http_build_query($questionsdata), [CURLOPT_ENCODING => 'gzip']);

	echo " fetched" . PHP_EOL;

	$obj=json_decode($response);

	if (array_key_exists('error_message', $obj))
	{
		echo $colors->getColoredString('Error: ' . $obj->{"error_message"}, "red") . PHP_EOL;
		continue;
	}

	$questions = $obj->{"items"};

	foreach ($questions as $question)
	{
		if ($question->{"downvoted"} == 1 || $question->{"upvoted"})
		{
			continue;
		}
		echo PHP_EOL . "(" . $colors->getColoredString(htmlspecialchars_decode($question->{"score"}, ENT_QUOTES), "grey") . ") " .$colors->getColoredString(htmlspecialchars_decode($question->{"title"}, ENT_QUOTES), "red") . PHP_EOL . PHP_EOL;

		echo $colors->getColoredString(mb_substr(htmlspecialchars_decode($question -> {"body_markdown"}, ENT_QUOTES), 0, 5000), "blue") . PHP_EOL . PHP_EOL;

		foreach ($question->{"tags"} as $qtag) {
			echo $colors->getColoredString("[" . $qtag . "] ", "white", "red");
		}

		echo PHP_EOL . PHP_EOL;

		$has_good_answer = 0;

		while ($has_good_answer == 0)
		{
			echo "vote? (u/d/s/h): ";

			$handle = fopen ("php://stdin","r");
			$line = fgets($handle);
	
			$response = trim($line);

			if ($response == "h")
			{
				echo "  h: help" . PHP_EOL . "  u: upvote" . PHP_EOL . "  d: downvote" . PHP_EOL . "  s: skip" . PHP_EOL . PHP_EOL;
			}
			if ($response == "s")
			{
				echo "skipping..." . PHP_EOL;
				$has_good_answer = 1;
			}

			if ($response == "d")
			{
				echo "downvoting question...";

				$downvoteURL = 'https://api.stackexchange.com/2.2/questions/' . $question->{"question_id"} . '/downvote';
				$downvoteData = array('site' => $site, 'preview' => 'false', 'id' => $question->{"question_id"}, 'key' => "6Z09liTt4uTQU*a4DYOXVQ((", 'access_token' => $access_token, "filter" => "!4(Yr(*8cVk(R9tqx4");
				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded, Accept-Encoding: gzip;q=0, compress;q=0\r\n",
						'method'  => 'POST',
						'content' => http_build_query($downvoteData),
						'ignore_errors' => true,
					),
				);
				$context = stream_context_create($options);
				$obj = json_decode(gzdecode(file_get_contents($downvoteURL, false, $context)));
				$result_question = $obj->{"items"}[0];
				echo $colors->getColoredString("score now at " . $result_question->{"score"} . PHP_EOL, "green");
				$has_good_answer = 1;
			}
			if ($response == "u")
			{
				echo "upvoting question...";

				$upvoteURL = 'https://api.stackexchange.com/2.2/questions/' . $question->{"question_id"} . '/upvote';
				$upvoteData = array('site' => $site, 'preview' => 'false', 'id' => $question->{"question_id"}, 'key' => "6Z09liTt4uTQU*a4DYOXVQ((", 'access_token' => $access_token, "filter" => "!4(Yr(*8cVk(R9tqx4");
				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded, Accept-Encoding: gzip;q=0, compress;q=0\r\n",
						'method'  => 'POST',
						'content' => http_build_query($upvoteData),
						'ignore_errors' => true,
					),
				);
				$context = stream_context_create($options);
				$obj = json_decode(gzdecode(file_get_contents($upvoteURL, false, $context)));
				$result_question = $obj->{"items"}[0];
				echo $colors->getColoredString("score now at " . $result_question->{"score"} . PHP_EOL, "green");
				$has_good_answer = 1;
			}
		}

	}
}
class Curl
{
  protected $info = [];
  
  public function exec($url, $setopt = array(), $post = array())
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20100101 Firefox/15.0.1');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    if ( ! empty($post))
    {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    if ( ! empty($setopt))
    {
      foreach ($setopt as $key => $value)
      {
        curl_setopt($curl, $key, $value);
      }
    }
    $data = curl_exec($curl);
    $this->info = curl_getinfo($curl);
    curl_close($curl);
    return $data;
  }
 
  public function getInfo()
  {
    return $this->info;
  }
}
class Colors {

	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}