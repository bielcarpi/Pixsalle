let selectedPhotoToDelete;
function showDeleteModal(photoId){
    selectedPhotoToDelete = photoId;
    $('#deletePhotoModal').modal();
}

function deletePhoto(){
    $.ajax({
        type: "DELETE",
        url: window.location.href,
        data: {id: selectedPhotoToDelete}
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200)
            location.reload();
        else
            document.getElementById("delete_photo_form_error").innerHTML = data.responseText;
    });

    return false; //Do not submit the form, we already handled the request
}

function addPhoto(){
    $.ajax({
        type: "POST",
        url: window.location.href,
        data: {url: document.getElementById("form_url").value}
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200)
            location.reload();
        else
            document.getElementById("add_photo_form_error").innerHTML = data.responseText;
    });

    return false; //Do not submit the form, we already handled the request
}

function generateQRCode(){
    let code = new URL(window.location.href).pathname.replace('/portfolio/album/', '');
    $.ajax({
        type: "POST",
        url: '/portfolio/album/code/' + code,
        data: {}
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200){
            //Code created successfully, we can now retrieve it
            $('#codeImg').attr('src', `/assets/img/codes/${code}.png`);
            $('#codeA').attr('href', `href="data:image/png;base64,/9j/` + data.responseText);
            $('#qrModal').modal('toggle');
        }
    });
}