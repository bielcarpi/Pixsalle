function onSubmitNewAlbum(){
    const title = document.getElementById("form_title").value;
    const cover = document.getElementById("form_cover").value;

    $.ajax({
        type: "POST",
        url: "/portfolio/album",
        data: {title: title, cover: cover},
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200)
            window.location.replace("/portfolio");
        else
            document.getElementById("form_error").innerHTML = data.responseText;
    });

    return false; //Do not submit the form, we already handled the request
}