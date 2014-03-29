<?php

/*
 * @package Simple Youtube Video Embedder/BBC
 * @version 2.0
 * @author LinuxPanda <linuxpanda@outlook.com>
 * @license Unlicense (http://unlicense.org)
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function SYTE_BBC(&$codes)
{
	global $modSettings;

	if (empty($modSettings['SYTE_enable']))
		return;

	array_push($codes,
		array(
			'tag' => 'auto_youtube',
			'type' => 'unparsed_content',
			'content' => '<iframe style="border:0;" width="642" height="392" src="$1" allowfullscreen></iframe>',
			'validate' => create_function('&$tag, &$data, $disabled', '
			$data = strtr($data, array(\'<br />\' => \'\'));
			$data = SYTE_AutoParseYoutubeLink($data);
			'),
			'disabled_content' => '$1',
			'block_level' => true,
		),
		array(
			'tag' => 'youtube',
			'type' => 'unparsed_content',
			'content' => '<iframe style="border:0;" width="642" height="392" src="https://www.youtube.com/embed/$1" allowfullscreen></iframe>',
			'validate' => create_function('&$tag, &$data, $disabled', '
			$data = strtr($data, array(\'<br />\' => \'\'));
			$pattern = \'~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@?&%=+\/\$_.-]*~i\';
			if (preg_match($pattern, $data, $matches))
				$data = $matches[1];'),
			'disabled_content' => 'https://www.youtube.com/watch?v=$1',
			'block_level' => true,
		),
		array(
			'tag' => 'yt',
			'type' => 'unparsed_content',
			'content' => '<iframe style="border:0;" width="642" height="392" src="https://www.youtube.com/embed/$1" allowfullscreen></iframe>',
			'validate' => create_function('&$tag, &$data, $disabled', '
			$data = strtr($data, array(\'<br />\' => \'\'));
			$pattern = \'~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@?&%=+\/\$_.-]*~i\';
			if (preg_match($pattern, $data, $matches))
				$data = $matches[1];'),
			'disabled_content' => 'https://www.youtube.com/watch?v=$1',
			'block_level' => true,
		)
	);
}

// Automatically parse youtube video/playlist links and generate the respective embed code
function SYTE_AutoParseYoutubeLink($data)
{
	// Check if youtube link is a playlist
	if (strpos($data, 'list=') !== false) {
		// Generate the embed code
		$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube.com/embed/videoseries?list=$1', $data);

		return $data;
	}
	// Check if youtube link is not a playlist but a video [with time identifier]
	if (strpos($data, 'list=') === false && strpos($data, 't=') !== false) {
		$time_in_secs = null;

		// Get the time in seconds from the time function
		$time_in_secs = ConvertTimeToSeconds($data);

		// Generate the embed code
		$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube.com/embed/$1?start=' . $time_in_secs, $data);

		return $data;
	}
	// If the above conditions were false then the youtube link is probably just a plain video link. So generate the embed code already.
	$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube.com/embed/$1', $data);

	return $data;
}

// Check for time identifier in the youtube video link and conver it into seconds
function ConvertTimeToSeconds($data)
{
	$time = null;
	$hours = null;
	$minutes = null;
	$seconds = null;

	$pattern_time_split = "([0-9]{1-2}+[^hms])";

	// Regex to check for youtube video link with time identifier
	$youtube_time = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*(t=((\d+h)?(\d+m)?(\d+s)?))~i';

	// Check for time identifier in the youtube video link, extract it and convert it to seconds
	if (preg_match($youtube_time, $data, $matches)) {
		// Check for hours
		if (isset($matches[4])) {
			$hours = $matches[4];
			$hours = preg_split($pattern_time_split, $hours);
			$hours = substr($hours[0], 0, -1);
		}

		// Check for minutes
		if (isset($matches[5])) {
			$minutes = $matches[5];
			$minutes = preg_split($pattern_time_split, $minutes);
			$minutes = substr($minutes[0], 0, -1);
		}

		// Check for seconds
		if (isset($matches[6])) {
			$seconds = $matches[6];
			$seconds = preg_split($pattern_time_split, $seconds);
			$seconds = substr($seconds[0], 0, -1);
		}

		// Convert time to seconds
		$time = (($hours*3600) + ($minutes*60) + $seconds);
	}
	
	return $time;
}

// Check if its a valid youtube link and enclose it within [youtube_auto] tags
function SYTE_CheckYoutubeLink($data)
{
	if (strpos($data, 'youtube.com') !== false || strpos($data, 'youtu.be') !== false)
		$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11,})[a-z0-9;:@#?&%=+\/\$_.-]*~i', '[auto_youtube]$0[/auto_youtube]', $data);

	return $data;
}

?>
