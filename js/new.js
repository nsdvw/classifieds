$(".first-level").on("click", function(event){
    if (!$(event.target).hasClass('category-link')) return;
    event.preventDefault();
    var id = $(event.target).attr('data-id');
    $.ajax('/ad/getcategories', {
        data:'id='+id,
        method:'POST',
        dataType:'json',
        success: function(data){
            var child = $('.second-level>ul');
            var grandchild = $('.third-level>ul');
            child.html('');
            grandchild.html('');
            if (data==false) {
                window.location.href = window.location.origin + '/ad/create/' + id;
            }
            $.each(data, function(i, val){
                child.append('<li><a class="category-link" href="'
                 + window.location.origin + '/ad/create/' + i
                 +'" data-id="'+i+'">'
                 + val + '</a></li>');
            });
        }
    });
});
$(".second-level").on("click", function(event){
    if (!$(event.target).hasClass('category-link')) return;
    event.preventDefault();
    var id = $(event.target).attr('data-id');
    $.ajax('/ad/getcategories', {
        data:'id='+id,
        method:'POST',
        dataType:'json',
        success: function(data){
            var child = $('.third-level>ul');
            child.html('');
            if (data==false) {
                window.location.href = window.location.origin + '/ad/create/' + id;
            }
            $.each(data, function(i, val){
                child.append('<li><a class="category-link" href="'
                 + window.location.origin + '/ad/create/' + i
                 +'" data-id="'+i+'">'
                 + val + '</a></li>');
            });
        }
    });
});
