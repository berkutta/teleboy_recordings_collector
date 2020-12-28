<?php

require("teleboy.inc.php");

$teleboy = new Teleboy;
$config = include("config.inc.php");

$teleboy->login();

$downloaded_recordings = json_decode(file_get_contents("downloaded_recordings.json"));
$downloaded_metadata = json_decode(file_get_contents("downloaded_metadata.json"));

if($downloaded_recordings == NULL) {
    $downloaded_recordings = [];
}

if($downloaded_metadata == NULL) {
    $downloaded_metadata = [];
}

foreach($teleboy->recordings() as $recording) {
    $file_prefix = substr($recording->begin, 0, -14)."_".$recording->station_label."_".$recording->slug."_".str_replace(' ', '_', $recording->subtitle);

    if(isset($recording->serie_season)) {
    	$file_prefix .= "_S".$recording->serie_season;
    }

    if(isset($recording->serie_episode)) {
        $file_prefix .= "_E".$recording->serie_episode;
    }

    if(!in_array($file_prefix, $downloaded_recordings)) {
        array_push($downloaded_recordings, $file_prefix);
        array_push($downloaded_metadata, $recording);

        // Just download if not already downloaded
        if(!glob($config["recording_path"].$file_prefix."*.mp4")) {
            file_put_contents($config["recording_path"].$file_prefix.".json", json_encode($recording));
            file_put_contents($config["recording_path"].$file_prefix.".mp4", fopen($teleboy->recording_download_url($recording->id), 'r'));
        }
    } else {
        echo "Already downloaded ".$file_prefix."\n";
    }
}

file_put_contents("downloaded_recordings.json", json_encode($downloaded_recordings, JSON_PRETTY_PRINT));
file_put_contents("downloaded_metadata.json", json_encode($downloaded_metadata, JSON_PRETTY_PRINT));

?>
