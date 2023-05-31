$('#search-form').submit(function(event) {
    event.preventDefault();
    window.location.replace("/search/"+$("#search-inpt").val());
});