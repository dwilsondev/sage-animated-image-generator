function generateImg(dNd = "") {
    /*

        CLEAR MESSAGES

    */
    let download = document.querySelector("#download");
    let errors = document.querySelector("#errors");
    

    download.style.cssText = "display: none";
    errors.style.cssText = "display: none";  

    /*

        GET FORM DATA

    */
    let formData = new FormData(document.querySelector('#form'));
        let fileField = document.querySelector('#file');
        let uploadType = document.querySelector('#uploadType').value;
        let fps = document.querySelector('#fps').value;
        let loopOption = document.querySelector('#loopOption').checked;
        let timestamp_start = document.querySelector('#timestamp_start').value;
        let timestamp_end = document.querySelector('#timestamp_end').value;
    let url = 'app/convert.php'
    formData.append('uploadType', uploadType);
    formData.append('fps', fps);
    formData.append('loopOption', loopOption);
    formData.append('timestamp_start', timestamp_start);
    formData.append('timestamp_end', timestamp_end);

    /*

        CHECK TIMESTAMPS

    */
    if(timestamp_start !== "" && /[0-9:]+/g.test(timestamp_start) == false) {
        alert("Bad start timestamp.");
        return false;
    }

    if(timestamp_end !== "" && /[0-9:]+/g.test(timestamp_end) == false) {
        alert("Bad end timestamp.");
        return false;
    }
    
    /*

        CHECK FOR FILES

    */
    if(!fileField.files[0] && !dNd) {
        errors.style.cssText = "display: block";
        errors.innerHTML = "Please choose a support file to upload.";
        return;
    }

    /*

        OLD FASHIONED UPLOAD

    */
    // If file was uploaded the old fashioned way, get file from selector.
    // else get from drag and drop data transfer.            
    if(fileField.files[0] !== undefined) {
        formData.append('file_0', fileField.files[0]);
    }

    /*

        DRAG N DROP

    */
    if(dNd) {
        dNd.preventDefault();

        if (dNd.dataTransfer.files) {
            for (var i = 0; i < dNd.dataTransfer.files.length; i++) {
                formData.append('file_'+i, dNd.dataTransfer.files[i]);
                //previewImage(dNd.dataTransfer.files[i]);
            }
        } else {
            alert("There was a problem adding files.");
            return;
        }
    }

    /*

        UPLOAD FILE AND RETURN LINK

    */
   if(fileField.files[0] !== undefined || dNd.dataTransfer.files[0] !== undefined) {
        document.querySelector('.upload').style.cssText = "display: none";
        document.querySelector('#submit').style.cssText = "display: none";
        document.querySelector('.wait').style.cssText = "display: block";

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
                    document.querySelector("#image_preview_image").src = result['link'];
                } else if(result['error']) {
                    // Errors were found, display error message.
                    errors.style.cssText = "display: block";
                    errors.innerHTML = result['error'];
                } else {
                    // Unknown error.
                    errors.style.cssText = "display: block";
                    errors.innerHTML = "There was a problem uploading file.";
                }

                // Show preview image. Reset hide upload message message and show submit button.
                document.querySelector('#image_preview').style.cssText = "display: block";
                document.querySelector('.upload').style.cssText = "display: block";

                if(manuel_submit == true) {
                    document.querySelector('#submit').style.cssText = "display: block";
                }
                
                document.querySelector('.wait').style.cssText = "display: none";
            })
    }
}

function dragOverHandler(event) {
    event.preventDefault();
}