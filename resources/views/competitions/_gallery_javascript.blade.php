<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
    var gallery_url_template = "{{ url('competition/entry-screenshot-gallery/{id}') }}";
    $(function() {
        $('.gallery-button').click(function(event) {
            event.preventDefault();
            var $t = $(this),
                par = $t.closest('[data-id]'),
                id = par.data('id'),
                obj = ({id}),
                gallery_query;
            var bb = bootbox.alert({
                title: 'View Entry Screenshots',
                message: '<div class="text-center"><img src="{{ asset("images/loading.gif") }}" alt="Loading..." /> Loading...</div>',
                size: 'large',
                buttons: {
                    ok: {
                        label: "Close",
                        className: "btn-default"
                    }
                },
                callback: function() {
                    if (gallery_query) gallery_query.abort();
                    this.find('.slider').remove();
                }
            });
            gallery_query = $.get(template(gallery_url_template, obj)).done(function(result) {
                var con = bb.find('.bootbox-body').html(result).find('.slider')[0];
                var slider = new $JssorSlider$(con, {
                    $AutoPlay: true,
                    $AutoPlayInterval: 4000,
                    $SlideDuration: 250,
                    $FillMode: 5,

                    $ThumbnailNavigatorOptions: {
                        $Class: $JssorThumbnailNavigator$,
                        $ChanceToShow: 2,
                        $SpacingX: 8,
                        $DisplayPieces: 10,
                        $ParkingPosition: 360
                    },

                    $ArrowNavigatorOptions: {
                        $Class: $JssorArrowNavigator$,
                        $AutoCenter: 2
                    }
                });
            });
        });
    });
</script>