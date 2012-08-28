(function($) {
    var $table = $('table.table');
    var $form = $('form.form-crud');

    $table.on('click', 'a.msi_admin_admin_change', function(e) {
        var $this = $(this);
        $.ajax($this.data('url'), {
            success: function(response) {
                $this.closest('td').html($(response).find('td#'+$this.closest('td').attr('id')).html());
            },
        });
        e.preventDefault();
    });

    $form.on('click', 'a.msi_admin_admin_delete', function(e) {
        if (!confirm('Are you sure you want to delete this entry?')) {
            e.preventDefault();
            return;
        }
    });

    $table.on('click', 'a.msi_admin_admin_delete', function(e) {
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

    $('.btn-select-all').on('click', function(e) {
        $(this).closest('.controls').siblings('.control-group').find('input').prop('checked', true);
        e.preventDefault();
    });

    $('.btn-select-none').on('click', function(e) {
        $(this).closest('.controls').siblings('.control-group').find('input').prop('checked', false);
        e.preventDefault();
    });

    // $('ul.thumbnails p').hide();

    // $('ul.thumbnails').on('mouseenter', 'li', function() {
    //     $(this).children('p').show();
    // });

    // $('ul.thumbnails').on('mouseleave', 'li', function() {
    //     $(this).children('p').hide();
    // });

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
