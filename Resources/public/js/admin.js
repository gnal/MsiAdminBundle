(function($) {
    "use strict";
    var $table = $('table.table');

    $table.on('click', 'a.msi_admin_admin_change', function(e) {
        var $this = $(this);
        $.ajax($this.data('url'), {
            success: function(response) {
                $this.closest('td').html($(response).find('td#'+$this.closest('td').attr('id')).html());
            }
        });
        e.preventDefault();
    });

    $table.on('click', 'a.msi_admin_admin_delete', function(e) {
        var $this = $(this);
        if (!window.confirm('Are you sure you want to delete this entry?')) {
            return;
        }
        $.get($this.data('url'));
        $this.closest('tr').remove();
        e.preventDefault();
    });

    $('form#limitForm select').on('change', function() {
        $(this).closest('form').submit();
    });

    $('.btn-select-all').on('click', function(e) {
        $(this).closest('.controls').next('.control-group').find('input').prop('checked', true);
        e.preventDefault();
    });

    $('.btn-select-none').on('click', function(e) {
        $(this).closest('.controls').next('.control-group').find('input').prop('checked', false);
        e.preventDefault();
    });
})(jQuery);
