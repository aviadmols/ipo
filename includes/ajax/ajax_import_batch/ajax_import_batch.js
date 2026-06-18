jQuery(window).on('load', function() {

    (function($) {

        // function to decode base64 coded string in jquery


        function import_batch($url) {

            $ajax_container = $('.ajax-import-msg-container');
            $ajax_logs = $('.ajax-import-msg-log');
            $ajax_container_parent = $('body .site-content');
            $ajax_container_parent.addClass('loading');
            $data = {
                action: 'ajax_import_batch',
                url: $url,
            };

            //console.log('starting ajax_import_batch ajax');


            $ajax_logs.html($ajax_logs.html() + '<br> starting ajax_import_batch ajax ' + $url);

            var start = new Date().getTime();

            // get all url parameters from $data['url] with their key names
            $url_params = $url.split('?')[1].split('&');
            $url_params = $url_params.map(function(n) {
                return n = n.split('=');
            });
            $url_params = Object.fromEntries($url_params);

            // Check if $url_params.num_posts exists
            if ($url_params.num_posts) {
                $num_posts = $url_params.num_posts;
            } else {
                $num_posts = 1;
            }

            // Check if $url_params.offset exists
            if ($url_params.offset) {
                $offset = $url_params.offset;
            } else {
                $offset = 0;
            }

            $.ajax({
                url: ajax_import_batch.ajaxurl,
                type: "post",
                dataType: "json",
                data: $data,
                success: function(response) {

                    $ajax_logs.html($ajax_logs.html() + '<br> AJAX Success ');

                    //console.log('AJAX success');

                    //console.log('AJAX messages');
                    //console.log(response.console_msg);

                    $ajax_container_parent.removeClass('loading');

                    var end = new Date().getTime();
                    var time = (end - start) / 1000;

                    $ajax_container.html('');
                    setTimeout(function() {
                        $ajax_container.html(response.messages);
                    }, 200);

                    $logs = $ajax_logs.html();

                    $ajax_logs.html($ajax_logs.html() + '<br>' + 'Time completed: ' + time + ' seconds');

                    // Time to wait is 1s
                    setTimeout(function() {
                        //$ajax_logs.html($ajax_logs.html() + '<br>' + '1s passed');



                        $offset = parseInt($offset) + parseInt($num_posts);
                        $new_url = 'https://www.ipo.co.il/ipo-extractor/';
                        $new_url = $new_url + '?raw=true&offset=' + $offset + '&num_posts=' + $num_posts;
                        import_batch($new_url)

                    }, 1000);

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('AJAX Failed ' + xhr.status);
                    console.log(xhr.responseText);
                    $ajax_container.removeClass('loading');

                    var end = new Date().getTime();
                    var time = (end - start) / 1000;

                    $ajax_logs.html($ajax_logs.html() + '<br>' + 'Error: ' + xhr.status + ' ' + thrownError + ' ' + time + ' seconds');

                    // Time to wait is 1s
                    setTimeout(function() {

                        $offset = parseInt($offset);
                        $new_url = 'https://www.ipo.co.il/ipo-extractor/';
                        $new_url = $new_url + '?raw=true&offset=' + $offset + '&num_posts=' + $num_posts;
                        import_batch($new_url)

                    }, 10000);

                }
            });


        }

        $('body').on('click', '#ajax-import-start', function(e) {

            $url = $('input[name="raw_url"]').val();

            e.preventDefault();
            import_batch($url);

        });

    })(jQuery);

});