(function($) {
    var $table = $('table.table');
    var $form = $('form.form-crud');

    $table.on('click', 'a.action-change', function(e) {
        var $this = $(this);

        e.preventDefault();
        $.ajax($this.data('url'), {
            success: function(response) {
                $this.closest('tr').html($(response).find('tr#el'+$this.closest('tr').data('id')).html());
            },
        });
        // if ($this.children().hasClass('badge-success')) {
        //     $this.children().removeClass('badge-success');
        // } else {
        //     $this.children().addClass('badge-success');
        // }
    });

    $form.on('click', 'a.action-delete', function(e) {
        if (!confirm('Are you sure you want to delete this entry?')) {
            e.preventDefault();
            return;
        }
    });

    $table.on('click', 'a.action-delete', function(e) {
        var $this = $(this);

        e.preventDefault();
        if (!confirm('Are you sure you want to delete this entry?')) {
            return;
        }
        $.get(
            $this.data('url')
        );
        $this.closest('tr').remove();
    });

    $('form.form-limit select').on('change', function() {
        $('form.form-limit').submit();
    });

    $('ul.thumbnails p').hide();

    $('ul.thumbnails').on('mouseenter', 'li', function() {
        $(this).children('p').show();
    });

    $('ul.thumbnails').on('mouseleave', 'li', function() {
        $(this).children('p').hide();
    });

    // $('form.form-search').on('keyup', function(e) {
    //     $this = $(this);
    //     // e.preventDefault();
    //     $.ajax($this.attr('action'), {
    //         type: 'GET',
    //         data: $this.serialize(),
    //         success: function(data) {
    //             var tableHtml = $(data).find('table.table').children();
    //             $table.html(tableHtml);
    //         }
    //     });
    // });

    // $table.on('click', 'div.pagination a', function(e) {
    //     $this = $(this);
    //     e.preventDefault();
    //     $.ajax($this.attr('href'), {
    //         type: 'GET',
    //         data: $this.serialize(),
    //         success: function(data) {
    //             var tableHtml = $(data).find('table.table').children();
    //             $table.html(tableHtml);
    //         }
    //     });
    // });
})(jQuery);
