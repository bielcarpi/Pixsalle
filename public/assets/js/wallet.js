function onSubmitAddFunds(){
    const fundsToAdd = document.getElementById("funds_input").value;

    $.ajax({
        type: "POST",
        url: "/user/wallet",
        data: {funds: fundsToAdd},
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200)
            window.location.replace("/user/wallet");
        else{
            document.getElementById("form_error").innerHTML = data.responseText;
            fundsToAdd.innerHTML = "";
        }
    });
}