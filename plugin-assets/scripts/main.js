(function ($) {
  $('html').on('click', '.ignite-test-email', function (e) {
    e.preventDefault();
    $('span.response').html('Working');
    var interval = setInterval(function () {
      $('span.response').html($('span.response').html() + '.');
    }, 1000);
    $.ajax({
      url: e.target.dataset.url,
      method: 'post',
      dataType: 'json',
      data: {action: 'ignite-test-email', test_email: $('.test_email').val()},
      success(res) {
        clearInterval(interval);
        $('span.response').html('Sent!');
        setTimeout(function () {
          $('span.response').html('');
        }, 2000);
      },
    })
  });
})(jQuery);
