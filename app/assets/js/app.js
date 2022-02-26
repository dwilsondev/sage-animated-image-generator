function generateImg(dNd = "") {
    /*

        CLEAR MESSAGES

    */
    let upload = document.querySelector('.upload');
    let download = document.querySelector("#download");
    let wait = document.querySelector(".wait");
    let preview = document.querySelector("#image_preview");
    let preview_image = document.querySelector("#image_preview_image");
    let submit = document.querySelector('#submit');
    let errors = document.querySelector("#errors");
    
    download.style.cssText = "display: none";
    preview.style.cssText = "display: none";  
    errors.style.cssText = "display: none";  

    /*

        GET FORM DATA

    */
    let formData = new FormData(document.querySelector('#form'));
        let fileField = document.querySelector('#file');
        let uploadType = document.querySelector('#uploadType').value;
        let resolution = document.querySelector('#resolution').value;
        let fps = document.querySelector('#fps').value;
        let loopOption = document.querySelector('#loopOption').checked;
        let timestamp_start = document.querySelector('#timestamp_start').value;
        let timestamp_end = document.querySelector('#timestamp_end').value;
    let url = 'app/convert.php'
    formData.append('uploadType', uploadType);
    formData.append('resolution', resolution);
    formData.append('fps', fps);
    formData.append('loopOption', loopOption);
    formData.append('timestamp_start', timestamp_start);
    formData.append('timestamp_end', timestamp_end);

    /*

        CHECK INPUTS

    */
    if(fps <= 0 || fps > 60) {
        errors.innerHTML = "Framerate should be between 1 and 60. (JS)";
        return false;
    }

    if(timestamp_start !== "" && /[0-9:]+/g.test(timestamp_start) === false) {
        errors.innerHTML = "Bad start timestamp. (JS)";
        return false;
    }

    if(timestamp_end !== "" && /[0-9:]+/g.test(timestamp_end) === false) {
        errors.innerHTML = "Bad end timestamp. (JS)";
        return false;
    }

    /*

        CHECK FOR FILES

    */
   // If no files upload from file input or drag n drop, tell user to upload file.
    if(!fileField.files && !dNd) {
        errors.style.cssText = "display: block";
        errors.innerHTML = "Please choose a file to upload. (JS)";
        return;
    }

    /*

        DRAG N DROP

    */
    // If files were uploaded via drag and drop, add them to form data array.
    if(dNd) {
        dNd.preventDefault();

        if (dNd.dataTransfer.files) {
            for (var i = 0; i < dNd.dataTransfer.files.length; i++) {
                formData.append('file_'+i, dNd.dataTransfer.files[i]);
            }
        } else {
            errors.innerHTML = "There was a problem adding files. (JS)";
            return;
        }
    } else if(fileField.files[0] !== undefined) {

        formData.append('file_0', fileField.files[0]);

    } else {
        errors.innerHTML = "There was a problem uploading file. (JS)";
        return;
    }

    /*

        UPLOAD FILE AND RETURN LINK

    */
   if(fileField.files[0] !== undefined || dNd.dataTransfer.files[0] !== undefined) {
        upload.style.cssText = "display: none";
        submit.style.cssText = "display: none";
        preview.style.cssText = "display: none";
        wait.style.cssText = "display: block";

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(result => {
                // Link means file was upload successfully, so
                // show download link.
                if(result['link']) {
                    download.href = result['link'];
                    download.style.cssText = "display: block";
                    
                    preview_image.src = result['link'];
                    preview.style.cssText = "display: block";
                } else if(result['error']) {
                    // Errors were found, display error message.
                    errors.innerHTML = result['error'];
                    errors.style.cssText = "display: block";
                } else {
                    // Unknown error.
                    errors.innerHTML = "There was a problem uploading file.";
                    errors.style.cssText = "display: block";
                }

                // Show upload button, hide wait message, and show submit button.
                upload.style.cssText = "display: block";
                wait.style.cssText = "display: none";
                
                if(manuel_submit === true) {
                    submit.style.cssText = "display: block";
                }
            })
    }
}

function dragOverHandler(event) {
    event.preventDefault();
}