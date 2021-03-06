<?php

    // File types supported by SAIG. You can remove the types you don't want.
    // defaults - ['mp4', 'png', 'jpeg', 'jpg', 'webp', 'gif', 'zip']
    $supported_filetypes = ['mp4', 'png', 'jpeg', 'jpg', 'webp', 'gif', 'tif', 'tiff', 'zip'];

    // Enable or disable upload options. Disabling will hide them from Web UI
    // and disable the conversions.
    $upload_options = [
        "animated_gifs" => "enabled",
        "animated_gifs_hq" => "disabled",
        "animated_gifs_to_video" => "enabled",
        "animated_webp" => "enabled",
        "animated_png" => "enabled",
    ];

    // Set the default convert option for Web UI.
    // Set to either "animated_gifs", "animated_gifs_hq", "animated_gifs_to_video"
    // "animated_webp", and "animated_png"
    $default_convert_option = "animated_gifs";

    // Set default resolution for Web UI. Se to "auto", "1080", "720", "480", or "360".
    $default_resolution_option = "auto";

    // Set default FPS option for Web UI.
    // Set to 1, 15, 25, 30, 50, 60.
    $default_fps_options = 30;

    // Set whether loop option is checked or unchecked by default.
    $default_loop_option = "checked";

    // Sets file size limit in megabytes.
    $filesize_limit = 512;

    // Enable or disable drag n drop upload and auto submit functions.
    // auto submit is for manuel uploads. drag and drop will always auto submit.
    $drag_n_drop = true;
    $auto_submit = true; // Displays a submit button if set to false.

    // Set binary environments. 
    // ffmpeg -- is required for all standard conversions except high quality gifs.
    // img2webp -- is optional for encoding WebP. (image/zip uploads only)
    // gifski -- is required for high quality Gifs.
    // 
    // Set to these options to "bin" to load from them from app/bin, set to empty 
    // to use binary added to your system path.
    $ffmpeg = "bin";
    $img2webp = "bin";
    $gifski = "bin";

    // Set encoder for WebP. Set webp_encoder to "img2webp" if you have it installed.
    // "ffmpeg" is the default. Enable or disable libwebp when using ffmpeg encoder.
    $webp_encoder = "ffmpeg";
    $libwebp = "enabled";

    // Enable or disable video timestamps. Used for trimming video uploads.
    // This feature is experimental. And while it works, it's not been 
    // thoroughly tested.
    $video_timestamps = "disabled";

    // Enable or disable renaming files when uploaded to the server. Setting this
    // to "true" can be good for security, but may ruin the generated image for 
    // certain image sequences.
    $rename_temp_files = false;