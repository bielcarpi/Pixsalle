function updateMembership(membershipCode){
    $.ajax({
        type: "POST",
        url: "/user/membership",
        data: {membership: membershipCode},
    }).always(function(data, statusText, xhr){
        if(xhr.status === 200)
            location.reload();
        else
            window.alert(data.responseText);
    });
}