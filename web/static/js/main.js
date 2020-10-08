/* -------------------------------------------------
 * CKEditor Config
 * ------------------------------------------------- */
ClassicEditor
    .create( document.querySelector( '#editor' ), {
        toolbar: ["undo", "redo", "|", "heading", "|", "bold", "italic", "blockQuote", "numberedList", "bulletedList", "|", "mediaEmbed"],
        mediaEmbed: {previewsInData: true}
    } )
    .then( editor => {
        // console.log( editor );
        // console.log( Array.from( editor.ui.componentFactory.names()));
    } )
    .catch( error => {
        console.log( error );
    } );


/* -------------------------------------------------
 * SHOW UPLOADED IMAGE
 * ------------------------------------------------- */
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#imageResult')
                .attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

$(function () {
    $('#upload').on('change', function () {
        readURL(input);
    });
});

/* -------------------------------------------------
 * SHOW UPLOADED IMAGE NAME
 * ------------------------------------------------- */
var input = document.getElementById( 'upload' );
var infoArea = document.getElementById( 'upload-label' );

input.addEventListener( 'change', showFileName );
function showFileName( event ) {
  var input = event.srcElement;
  var fileName = input.files[0].name;
  infoArea.textContent = 'File name: ' + fileName;
}
