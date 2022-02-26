<?php

    // File types supported by AIG. You can remove the types you don't want.
    // defaults - ['mp4', 'png', 'jpeg', 'jpg', 'webp', 'gif', 'zip']
    $supported_filetypes = ['mp4', 'png', 'jpeg', 'jpg', 'webp', 'gif', 'zip'];

    // Enable or disable upload options.
    $upload_options = [
        "animated_gifs" => "enabled",
        "animated_gifs_hq" => "disabled",
        "animated_gifs_to_video" => "disabled",
        "animated_webp" => "enabled",
        "animated_png" => "enabled",
    ];

    // Set the default convert option for Web UI.
    // Set to either "animated_gifs", "animated_gifs_hq", "animated_gifs_to_video"
    // "animated_webp", and "animated_png"
    $default_convert_option = "animated_gifs";

    // Set default FPS option for Web UI.
    // Set to 1, 15, 25, 30, 50, 60. Note, you can add additional options
    // in the form. Max fps is 60.
    $default_fps = 30;

    // Set whether loop option is checked or unchecked by default.
    $loop_infinite_checked = true;

    // Sets file size limit in megabytes.
    $filesize_limit = 10;

    // Enable or disable drag n drop upload and auto submit functions.
    // auto submit is for manuel uploads. drag and drop will always auto submit.
    $drag_n_drop = true;
    $auto_submit = false; // Displays a submit button if set to false.

    // Set binary environments. 
    // ffmpeg -- is required for all standard conversions except high quality gifs.
    // img2webp -- is optional for encoding WebP. (image/zip uploads only)
    // apngasm -- is optional for encoding APNG. Allows for looping and better encoding. (image/zip uploads only)
    // gifski -- is required for high quality Gifs.
    // 
    // Set to these options to "app" to load from them from app/bin, set to empty 
    // to use binary added to your system path.
    $ffmpeg = "app";
    $img2webp = "app";
    $apngasm = "app";
    $gifski = "app";

    // Set encoder for WebP and APNG. "ffmpeg" is the default
    // Set webp_encoder to "img2webp" if you have it installed.
    // Set apng_encoder to "apngasm" if you have it installed.
    $webp_encoder = "ffmpeg";
    $apng_encoder = "ffmpeg";

    // Enable or disable timestamps. Used for trimming video uploads.
    // This feature is experimental. And while it works, it's not been 
    // thoroughly tested.
    $video_timestamps = "disabled";

    // AIG renames all uploaded files to random names for security prior
    // to making animation frames. Setting to false disables this and may allow for 
    // better animation results.
    $rename_temp_files = true;