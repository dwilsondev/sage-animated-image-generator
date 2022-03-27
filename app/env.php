<?php

    if($ffmpeg == "bin") {
        $ffmpeg = realpath("bin") . DIRECTORY_SEPARATOR . "ffmpeg";
    } else {
        $ffmpeg = "ffmpeg";
    }

    if($img2webp == "bin") {
        $img2webp = realpath("bin") . DIRECTORY_SEPARATOR  ."img2webp";
    } else {
        $img2webp = "img2webp";
    }

    if($gifski == "bin") {
        $gifski = realpath("bin") . DIRECTORY_SEPARATOR . "gifski";
    } else {
        $gifski = "gifski";
    }