jQuery(window).on('load', function() {

    (function($) {

        // function to decode base64 coded string in jquery


        function process_batch($offset, $amount, $times = 1) {



            $ajax_container = $('.ajax-posts-process-msg-container');
            $ajax_logs = $('.ajax-posts-process-msg-log');
            $ajax_container_parent = $('body .site-content');
            $ajax_container_parent.addClass('loading');
            $data = {
                action: 'ajax_process_posts',
                offset: $offset,
                amount: $amount,
            };

            if (!$times) {
                $ajax_logs.html($ajax_logs.html() + '<br> ENDED ');
                return false;
            }

            $ajax_logs.html($ajax_logs.html() + '<br> starting ajax_process_posts ajax ');

            var start = new Date().getTime();

            $.ajax({
                url: ajax_process_posts.ajaxurl,
                type: "post",
                dataType: "json",
                data: $data,
                success: function(response) {

                    $ajax_logs.html($ajax_logs.html() + '<br> AJAX Success ');

                    $ajax_container_parent.removeClass('loading');

                    var end = new Date().getTime();
                    var time = (end - start) / 1000;

                    $ajax_container.html('');

                    $ajax_logs.html($ajax_logs.html() + response.logs);
                    $ajax_logs.html($ajax_logs.html() + '<br>' + 'Time completed: ' + time + ' seconds');

                    // Time to wait is 1s
                    setTimeout(function() {
                        //$ajax_logs.html($ajax_logs.html() + '<br>' + '1s passed');

                        $times = parseInt($times) - 1;

                        $offset = parseInt($offset) + parseInt($amount);
                        $('input[name="offset"]').val(parseInt($offset));
                        $('input[name="amount"]').val(parseInt($amount));
                        $('input[name="times"]').val(parseInt($times));

                        process_batch($offset, $amount, $times)

                    }, 1000);

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('AJAX Failed ' + xhr.status);
                    console.log(xhr.responseText);
                    $ajax_container.removeClass('loading');

                    var end = new Date().getTime();
                    var time = (end - start) / 1000;

                    $ajax_logs.html($ajax_logs.html() + '<br>' + 'Error: ' + xhr.status + ' ' + thrownError + ' ' + time + ' seconds');

                }
            });


        }

        $('body').on('click', '#ajax-posts-process-start', function(e) {

            $offset = $('input[name="offset"]').val();
            $amount = $('input[name="amount"]').val();
            $times = $('input[name="times"]').val();

            e.preventDefault();
            process_batch($offset, $amount, $times);

        });

    })(jQuery);

});