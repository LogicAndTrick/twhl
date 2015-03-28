$(document).on('click', '.video-content .uninitialised', function(event) {
    var $t = $(this),
      ytid = $t.data('youtube-id'),
       url = 'https://www.youtube.com/embed/' + ytid + '?autoplay=1&rel=0',
     frame = $('<iframe></iframe>').attr({ src: url, frameborder: 0, allowfullscreen: ''}).addClass('caption-body');
    $t.replaceWith(frame);
});