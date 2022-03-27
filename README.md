# Sage Animated Image Generator
Webapp for creating Animated GIFs, PNGs, and WebPs.

<img src="https://cdn.drewilson.dev/public/for_github/sage-logo.png" style="width:10%; margin: auto; margin-bottom: 2%; display: block; " alt="logo">

![screenshot](https://dre-dev.s3.us-east-2.amazonaws.com/public/for_github/saig-screenshot.png)

# About
Sage Animated Image Generator (SAIG) is a simple Webapp for creating animated GIFs, animated PNGs, and animated WebP images. It can also convert animated GIFs to video.

# Features
* Generate animated GIFs, WebP, and PNGs from PNG/JPG/TIF/WebP stills, mp4 video, or a zip containing images.
* Convert mp4 video to animated GIFs.
* Convert animated GIFs to animated WebP and PNGs.
* Generate high quality animated GIFs. (requires gifski)
* Options for animation resolution, framerate, and single/infinite looping.
* Supports img2webp encoding for WebP.
* Supports drag and drop file uploads.
* Create animated images from a portion of video using start and stop timestamps. (experimental)
* Mobile friendly.

# Installation
SAIG requires a Webserver with PHP, and [ffmpeg](https://github.com/FFmpeg/FFmpeg).

Download and extract SAIG ZIP and place it on your Webserver.

If you don't have ffmpeg installed on your system, download it and place it in the app/bin folder named `ffmpeg`.

And that's it!

Load up the page in your Web browser and upload images or video to convert them into animated images.

# Configure PHP (recommended)
* If you enable drag and drop, increase `max_file_uploads` in your php.ini. The default is 20.
* You may want to increase `max_execution_time` in your `php.ini`. High quality gifs and animated PNGs can take a while to process depending on the upload options.
* Increase `upload_max_filesize` in your php.ini to allow larger uploads.


# High Quality GIFs (with gifski)
[example](https://cdn.drewilson.dev/public/for_github/up-for-amazing.gif)

SAIG can generate high quality animated GIFs with the help of [gifski](https://gif.ski/). Simply download and place `gifski` inside the app/bin folder.

If you have gifski installed on your system, set the `$gifski` variable in the config file to an empty string.

Set the `$upload_options` `animated_gifs_hq` option to `enabled`.

Note: Max framerate for high quality gifs is 50fps.

# Better WebP Encoding (with img2webp)
SAIG uses ffmpeg to create animated WebP images, but you can change this to [img2webp](https://developers.google.com/speed/webp/download), a much better encoder for WebP. img2webp is included as part of the libwebp package from Google. Simply download libwebp, extract the img2webp binary and place it in the app/bin folder. Or in your system path.

If you have img2webp installed on your system, set the `$img2webp` variable in the config file to an empty string.

Set the `$webp_encoder` variable to `img2webp`.

# ZIP Upload
You can upload a zip file containing PNG, JPG, WebP, and TIF images to generate animated images.

# Animated GIFs To Video
You can upload an animated GIF and have it converted mp4 video.

# Video Timestamps (experimental)
This feature allows you to input start and stop timestamps for video uploads. SAIG will then make an animated image from that portion of video.

# Configuration
You can change SAIG options in the `config.php` file inside the app folder.

### Supported Filetypes 
Set which files are permitted to be uploaded. If you remove a file type, those files will not be uploaded. Default types are `mp4`, `png`, `jpg`, `jpeg`, `webp`, `gif`, `tif`, `tiff`, and `zip`.

Note: When zip files are uploaded, all files extracted that are not PNG, JPG, WebP, or TIF are deleted.

### Upload Options
Set the which upload/conversions are allowed. For example, setting `animated_webp` to `disabled` will hide the option in the Web UI, and disable WebP conversions.

### Default Convert Option
Sets the default selected convert option in the Web UI. Can be set to:
* `animated_gifs`
* `animated_gifs_hq` (if gifski is installed)
* `animated_gifs_to_video`
* `animated_webp`
* `animated_png`

### Default Resolution Option
Sets the default selected resolution in the Web UI. Can be set to:
* `auto`
* `1080`
* `720`
* `480`
* `360`

### Default FPS Option
Sets the default selected FPS option in the Web UI. Can be set to:
* 60
* 50
* 30
* 25
* 15
* 1

Note: High quality GIFs are limited to 50fps.

### Default Loop Option
Sets whether the loop option is checkbox by default. Set to `checked` to check by default. Or empty to uncheck by default.

### Filesize Limit
Set the maximum size for the total files uploaded in megabytes.

### Drag N Drop
Set to `true` to enable file drag and drop onto the upload button. This will auto submit files. Set to `false` to disable.

### Auto Submit
Set to `true` to allow files to be auto submitted when chosen from the upload dialog box. Set to `false` to disable. If auto submit is disabled, a submit button will be displayed in the form.

### Binary Environments
Tell SAIG whether to use local ffmpeg, gifski, and img2webp binaries or use system binaries. If these are set to `bin`, SAIG will look for the binary files in app/bin. Otherwise it will use the binaries in your Windows System Path or Linux /etc/bin folder.

You can manually set the binary locations in `env.php` file in the app folder.

The defaults are:
* `$ffmpeg = "bin"`
* `$img2webp = "bin"`
* `$gifski = "bin"`

### WebP Encoder (image/zip uploads only)
Sets the encoder for WebP. The default is `ffmpeg`. If you have img2webp installed, set this to `img2webp`.

Note: Make sure images are of the same resolution when using img2webp, else you might get errors.

### libwebp
Set whether ffmpeg should use libwebp when creating animated WebP. Set to `enabled` or `disabled`.

### Video Timestamps (experimental)
Enable or disable video timestamps feature for video uploads. Set to `enabled` or `disabled`.

Timestamp format can either be `00:00` or `00.00` for minute and seconds.

This feature is experimental, and while it works, it hasn't been thoroughly tested and is disabled by default.

### Rename Temp Files
Set whether SAIG should rename files to random strings when uploaded prior to creating animations. Disabling this leaves the file names untouched and can make for better animation results. Especially for a sequence of images. Set to `true` to enable or `false` to disable.

# Supported Upload Filetypes
* .png
* .jpg/jpeg
* .tif/tiff
* .webp
* animated .gif (for video only)
* .mp4
* .zip (containing a mix of PNG, JPG, WebP, and TIF images)

# Output Filetypes
* Animated GIFs (as .gif)
* Animated WebP (as .webp)
* Animated PNG (as .png)
* Video (as .mp4)

# Webapp
SAIG can be turned into a Webapp for desktop and mobile. A service worker is set up. All you have to do is change the src and start_url in the sage.webmanifest file located in app/assets/js to the domain of your site. You can use relative or absolute paths. Then uncomment this line in index.php:

`<!-- service worker <link rel="manifest" href="app/assets/js/sage.webmanifest"> -->`

You must have a secure website with SSL setup in order for the service worker to work.

If you do this, I suggest blocking access to the Webapp so that only devices in your network can use the app. You can block access with an .htaccess file at the root of your SAIG folder with the following code:

```
<LIMIT GET>
	Order deny,allow
	Deny from all
	allow from localhost
	allow from 127.0.0.1
	allow from 192.168
</LIMIT>
```

# Limitations
* img2webp encoding does not support custom framerate.
* libwebp ffmpeg encode will always loop.
* High quality gifs are limited to a max of 50fps.

# Known Bugs
* There are some unexplained errors when generating high quality gifs from images/zip of images that are not already sequentially named. It's recommended you generate image stills before uploading.
* img2webp encoding may throw errors if all images uploaded are not the same resolution.

# Note
* This was just a fun project. I do not recommend using this for a commercial site unless you want to rewrite some of the code.
* High quality GIFs, WebP, and PNG conversions can take a very long time to process depending on the size(s) of the files. You should increase the max execution time for PHP.
* I have not written any lock checks for this app yet. So once a conversion starts, it will continue processing on the server even if the user closes the tab. Also, multiple process can run at the same time. This can be bad if you have tons of requests.
  
# Created In
* PHP
* SCSS
* JavaScript

# Created With
* [ffmpeg](https://www.ffmpeg.org/)
* [gifski](https://gif.ski/)
* [img2webp](https://developers.google.com/speed/webp/download) (from libwebp)
  
# Special Thanks To [kornelski](https://github.com/kornelski) for making Gifski.
