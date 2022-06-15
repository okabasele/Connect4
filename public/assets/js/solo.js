function pictureChange()
{
    var playerOneImage = document.getElementById("icon-player-one").src;
    // console.log(playerOneImage);

    var playerOneColor = document.getElementById("playerOneColor").value
    // console.log(playerOneColor);

    //change images
      document.getElementById("icon-player-one").src=document.getElementById("icon-player-two").src;
      document.getElementById("icon-player-two").src = playerOneImage;

      //change hidden input values
      document.getElementById("playerOneColor").value=document.getElementById("playerTwoColor").value;
      document.getElementById("playerTwoColor").value = playerOneColor;
    
}