<?php

    if($ffmpeg == "bin") {
        $ffmpeg = realpath("bin")."\\ffmpeg";
    } else {
        $ffmpeg = "ffmpeg";
    }

    if($img2webp == "bin") {
        $img2webp = realpath("bin")."\\img2webp";
    } else {
        $img2webp = "img2webp";
    }

    if($gifski == "bin") {
        $gifski = realpath("bin")."\\gifski";
    } else {
        $gifski = "gifski";
    }