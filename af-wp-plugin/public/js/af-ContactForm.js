jQuery( 'form[name="contact-me"]' ).on( 'submit', function() {
    var form_data = jQuery( this ).serializeArray();
     
    // Here we add our nonce (The one we created on our functions.php. WordPress needs this code to verify if the request comes from a valid source.
    form_data.push( { "name" : "security", "value" : ajax_nonce } );
 
    // Here is the ajax petition.
    jQuery.ajax({
        url : ajax_url, // Here goes our WordPress AJAX endpoint.
        type : 'post',
        data : form_data,
        success : function( response ) {
            // You can craft something here to handle the message return
            alert( response );
        },
        fail : function( err ) {
            // You can craft something here to handle an error if something goes wrong when doing the AJAX request.
            alert( "There was an error: " + err );
        }
    });
     
    // This return prevents the submit event to refresh the page.
    return false;
});
