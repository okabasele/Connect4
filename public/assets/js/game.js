$(document).ready(function(){

    $(".dropDisc").eq(0).css("visibility", "visible");
    $(".dropDisc").not($(".dropDisc").eq(0)).css("visibility", "hidden");

$(".column").each(function( index ) {
    $(this).hover(function(){
       $(".dropDisc").eq(index).css("visibility", "visible");
       $(".dropDisc").not($(".dropDisc").eq(index)).css("visibility", "hidden");

	});
});

});