//Create basic post editor

(function($){

//Final processing function

$( '#editor' ).on( 'submit', function(e) {
   e.preventDefault();
var title = $( '#title' ).val();
var content = $( '#content' ).val();
console.log( content );
   var JSONObj = {
"title"
"content_raw"
"status"
};
:title,
:content,
:'publish'
   var data = JSON.stringify(JSONObj);
   var postID = $( '#post-id').val();
   if ( undefined !== postID ) {
url += '/';
       url += postID;
}
   $.ajax({
       type:"POST",
url: url,
dataType : 'json',
data: data,
       beforeSend : function( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', MY_POST_EDITOR.nonce );
},
success: function(response) {
           alert( MY_POST_EDITOR.successMessage );
           getPostsByUser( response.ID );
results( response );
},
failure: function( response ) {
           alert( MY_POST_EDITOR.failureMessage );
}
});
});


})(jQuery);
