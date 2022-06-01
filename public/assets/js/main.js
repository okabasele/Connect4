$(document).ready(function(){

    // console.log($(".column"));
    // console.log($(".dropDisc"));

$(".column").each(function( index ) {
    $(this).hover(function(){
       $(".dropDisc").eq(index).css("visibility", "visible");
       $(".dropDisc").not($(".dropDisc").eq(index)).css("visibility", "hidden");

	});
});

});