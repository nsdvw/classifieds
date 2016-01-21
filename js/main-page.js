$(function () {
    $(".root").on('click', 'a', function (event) {
        event.preventDefault();
        var root = $(this).attr('data-root');
        $(".children").not("[data-root=" + root + "]").slideUp();
        $(".children[data-root=" + root + "]").slideToggle();
    });
});
