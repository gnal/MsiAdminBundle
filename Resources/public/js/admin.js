(function($) {
    "use strict";
    var $table = $('table.table');
    var changeLoading = false;

    $table.on('click', 'a.msi_admin_admin_change', function(e) {

        if (changeLoading === true) {
            return;
        }

        changeLoading = true;
        var $this = $(this);
        var iconTrue = $this.data('icon-true');
        var iconFalse = $this.data('icon-false');
        var BadgeTrue = $this.data('badge-true');
        var BadgeFalse = $this.data('badge-false');

        $this.children('span').html('<img src="/bundles/msiadmin/img/ajax-loader2.gif" alt="0">');

        $.ajax($this.data('url'), {
            success: function() {
                if ($this.children('span').hasClass(BadgeTrue)) {
                    var i = '<i class="icon-white"><span class="hide">0</span></i>';
                    $this.children('span')
                        .empty()
                        .removeClass(BadgeTrue)
                        .addClass(BadgeFalse)
                        .html(i)
                        .children()
                        .removeClass(iconTrue)
                        .addClass(iconFalse);
                } else {
                    var i = '<i class="icon-white"><span class="hide">1</span></i>';
                    $this.children('span')
                        .empty()
                        .removeClass(BadgeFalse)
                        .addClass(BadgeTrue)
                        .html(i)
                        .children()
                        .removeClass(iconFalse)
                        .addClass(iconTrue);
                }

                changeLoading = false;
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
