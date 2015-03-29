$(window).ready(function(){
    initiate_geolocation();
});

function initiate_geolocation() {
    navigator.geolocation.getCurrentPosition(handle_geolocation_query, handle_errors);
}

function handle_geolocation_query(position){

    $.ajax({
        type: "POST",
        url: "/planner/async/update_settings",
        data: {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
        },
        dataType: "json",
        success: function(response) {
            console.log(response);
        },
        error: function(response) {
            console.log(response);
        }
    });
}

function handle_errors(error)
{
    switch(error.code)
    {
        case error.PERMISSION_DENIED:  console.log("User did not share geolocation data.");
            break;

        case error.POSITION_UNAVAILABLE:  console.log("Could not detect current position.");
            break;

        case error.TIMEOUT:  console.log("Retrieving position timed out.");
            break;

        default:  console.log("Unknown error.");
            break;
    }
}
