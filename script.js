$(function() {
  $('.fullscreenImage').fullscreenImage({
    imagePath: 'example.jpg',
    rootPath: '/var/www/fullscreenImage/',
    onReady: function() {
      console.log('Hey, I am ready! :D');
    }
  });
});