<?php

    require_once "app/config.php";

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Animated Image Generator</title>

        <link rel="stylesheet" href="app/assets/css/style.css">
        <script src="app/assets/js/app.js"></script>

        <!-- min 
        <link rel="stylesheet" href="app/assets/css/style.min.css">
        <script src="app/assets/js/app.min.js"></script>
        -->

        <script>
            <?php if($auto_submit == false) : ?>
                manuel_submit = true;
            <?php else : ?>
                manuel_submit = false;
            <?php endif; ?>
        </script>
    </head>
    <body>
        <header>
            <h1><a href="./">Animated Image Generator <span>v1.0</span></a></h1>
        </header>

        <main>
            <?php if($ffmpeg == "app" && !file_exists("app/bin/ffmpeg.exe")) : ?>
                <div id="no-ffmpeg">
                    <h2>FFmpeg wasn't found. Please download <a href="https://ffmpeg.org/download.html" target="_blank">FFmpeg</a> and place it in the app/bin folder. <br> If you have it installed on your system, set the ffmpeg option to empty in app/config.php</h2>
                </div>
            <?php else : ?>
                <div class="content">
                    <a id="download" href="" download>Download Image</a>

                    <span id="errors"></span>

                    <form id="form" method="POST" action="app/convert.php" enctype="multipart/form-data">
                        <div class="convert-sections">
                            <div class="box upload">
                                <div id="image_preview">
                                    <h2>Preview</h2>
                                    
                                    <img id="image_preview_image" src="" alt="animated image preview">        
                                </div>

                                <h2>Upload File</h2>

                                <div class="inner">
                                    <label id="drop_zone" <?php if($drag_n_drop == true) : ?>ondrop="generateImg(event);"  ondragover="dragOverHandler(event);" <?php endif; ?> for="file">
                                        <img id="upload_img" src="app/assets/img/upload.png" alt="upload button image">      
                                    </label>

                                    <input id="file" type="file" required <?php if($auto_submit == true) : ?>onchange="generateImg();"<?php endif; ?> required>
                                    
                                    <p class="directions"><?php if($drag_n_drop == true) : ?>Drag N drop images or click to upload. <br><?php endif; ?></p>
                                </div>
                            </div>

                            <div class="box wait">
                                <h2>Please Wait...</h2>

                                <div class="inner">
                                    <img src="app/assets/img/stopwatch.png" alt="wait for upload image">      
                                </div>
                            </div>

                            <div class="box options">
                                <h2>Options</h2>

                                <div class="inner">
                                    <label>Convert Type</label>
                                    <select id="uploadType" onchange="loopCheck();">
                                        <?php if($upload_options['animated_gifs'] == "enabled") : ?>
                                        <option value="gif" <?php if($default_convert_option == "animated_gifs") { echo "selected"; } ?>>Animated GIF</option>
                                        <?php endif; ?>

                                        <?php if($upload_options['animated_gifs_hq'] == "enabled") : ?>
                                        <option value="gif-hd" <?php if($default_convert_option == "animated_gifs_hq") { echo "selected"; } ?>>Animated GIF (High Quality)</option>
                                        <?php endif; ?>

                                        <?php if($upload_options['animated_gifs_to_video'] == "enabled") : ?>
                                        <option value="video" <?php if($default_convert_option == "animated_gifs_to_video") { echo "selected"; } ?>>Animated GIF To Video</option>
                                        <?php endif; ?>

                                        <?php if($upload_options['animated_webp'] == "enabled") : ?>
                                        <option value="webp" <?php if($default_convert_option == "animated_webp") { echo "selected"; } ?>>Animated WebP</option>
                                        <?php endif; ?>

                                        <?php if($upload_options['animated_apng'] == "enabled") : ?>
                                        <option value="apng" <?php if($default_convert_option == "animated_apng") { echo "selected"; } ?>>Animated PNG</option>
                                        <?php endif; ?>
                                    </select>

                                    <label>Framerate</label>
                                    <select id="fps">
                                        <option value="60" <?php if($default_fps == 60) { echo "selected"; } ?>>60fps</option>
                                        <option value="50" <?php if($default_fps == 50) { echo "selected"; } ?>>50fps</option>
                                        <option value="30" <?php if($default_fps == 30) { echo "selected"; } ?>>30fps</option>
                                        <option value="25" <?php if($default_fps == 25) { echo "selected"; } ?>>25fps</option>
                                        <option value="15" <?php if($default_fps == 15) { echo "selected"; } ?>>15fps</option>
                                        <option value="1" <?php if($default_fps == 1) { echo "selected"; } ?>>1fps</option>
                                    </select>

                                    <?php if($video_timestamps == "enabled") : ?>
                                    <label>Start/Stop Timestamps <span>1 minute limit.</span></label>
                                    <div class="timestamps">
                                        <input id="timestamp_start" type="text" placeholder="00:05" maxlength="5" minlength="5">
                                        <input id="timestamp_end" type="text" placeholder="00:10" maxlength="5" minlength="5">
                                    </div>
                                    <?php else : ?>
                                        <input id="timestamp_start" type="hidden" value="">
                                        <input id="timestamp_end" type="hidden" value="">
                                    <?php endif; ?>

                                    <label>Loop Infinitely? <?php if($apng_encoder !== "apngasm") : ?><span> Only GIF and WebP supported.<?php endif; ?></span></label>
                                    <input id="loopOption" type="checkbox" <?php if($loop_infinite_checked == true) { echo "checked"; } ?>>

                                    <span id="submit" onclick="generateImg();">Generate</span>
                                </div>
                            </div>
                        </div>
                    </form>

                    <p class="directions">                 
                    Supported files: <?php foreach($supported_filetypes as $type) { echo strtoupper($type)." "; } ?><br>
                    Size limit: <?php echo $filesize_limit; ?> megs or less.</p>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>Created by <a href="https://drewilson.dev" target="_blank">Dre Wilson</a> with help from <a href="https://www.ffmpeg.org/" target="_blank">ffmpeg</a>, <a href="http://apngasm.sourceforge.net/" target="_blank">apngasm</a>, and <a href="https://gif.ski/" target="_blank">gifski</a>.</p>
            <p>Special thanks to <a href="https://github.com/kornelski" target="_blank">kornelski</a>, creator of gifski.</p>
        </footer>
    </body>
</html>

<script>
<?php if($auto_submit == true) : ?>
document.querySelector('#submit').style.cssText = "display: none";
<?php endif; ?>

function loopCheck() {
    let uploadType = document.querySelector('#uploadType');
    let fps = document.querySelector('#fps');
    let loopOption = document.querySelector('#loopOption');

    if(uploadType.value == "apng") {
        <?php if($apng_encoder !== "apngasm") : ?>
        loopOption.checked = false;
        loopOption.disabled = true;
        <?php else : ?>
        fps.value = 30;
        fps.disabled = true;
        <?php endif; ?>
    } else if(uploadType.value == "video") {
        loopOption.checked = false;
        loopOption.disabled = true;
    } else {
        loopOption.checked = true;
        loopOption.disabled = false;
    }
}
</script>