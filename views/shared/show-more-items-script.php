<script>
    jQuery(document).ready(function () {
        var showMoreText = [];
        jQuery('.related-items-show-more a').click(function() {
            var groupId = '#' + this.id;
            var itemClassName = '.' + this.id + '-extra';
            if (jQuery(itemClassName).css('display') === "none")
            {
                // Remember the 'Show more' message because it says how many more items to show.
                showMoreText[groupId] = jQuery(groupId).text();
                jQuery(groupId).text("<?php echo __('Show less'); ?>");
                jQuery(itemClassName).slideDown("fast");
            }
            else
            {
                // Restore the original 'Show more' message.
                jQuery(groupId).text(showMoreText[groupId]);
                jQuery(itemClassName).fadeOut("fast");
            }

            // Prevent the browser from moving to the top of the page as though you just linked to a new page.
            return false;
        });
    });
</script>
