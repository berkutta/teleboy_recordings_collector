<?php

$config = include("config.inc.php");

$downloaded_recordings = json_decode(file_get_contents("downloaded_recordings.json"));

$converted_recordings = json_decode(file_get_contents("converted_recordings.json"));

if($converted_recordings == NULL) {
    $converted_recordings = [];
}

foreach($downloaded_recordings as $file_prefix) {
    if(!in_array($file_prefix, $converted_recordings)) {
        array_push($converted_recordings, $file_prefix);

        $original_path = $config["recording_path"].$file_prefix.".mp4";
        $converted_path = $config["recording_path"].$file_prefix."_streaming.mp4";

        if(glob($original_path) && !glob($converted_path)) {
            $command = "ffmpeg -i \"".$original_path."\" -movflags faststart -movflags faststart -c:v copy -c:a copy -c:s copy -c:d copy -c:t copy -map 0 \"".$converted_path."\"";
            
            $output = null;
            $retval = null;
            exec($command, $output, $retval);

            if($retval == 0) {
                echo "Converted ok, trying to delete now ".$file_prefix."\n";
                unlink($original_path);
            } else {
                echo "Something bad happened during convertsion of ".$file_prefix."\n";
            }
        }
    }
}

file_put_contents("converted_recordings.json", json_encode($converted_recordings, JSON_PRETTY_PRINT));
