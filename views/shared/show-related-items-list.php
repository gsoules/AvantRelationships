<div id="related-items">
    <ul>
        <?php
        /* @var $relatedItemsListView RelatedItemsListView */
        /* @var $directKid RelatedItemsTreeNode */
        /* @var $indirectSubtree RelatedItemsTreeNode */

        $relatedItemsTree = $relatedItemsModel->getRelatedItemsTree();
        $rootNode = $relatedItemsTree->getRootNode();
        $directKids = $rootNode->getKids();

        foreach ($directKids as $directKid)
        {
            if ($directKid->hasSubtrees())
            {
                echo $relatedItemsListView->emitSection($directKid);

                $indirectSubtrees = $directKid->getKids();
                foreach ($indirectSubtrees as $indirectSubtree)
                {
                    $indirectSubtreeKids = $indirectSubtree->getKids();
                    if (count($indirectSubtreeKids) == 0)
                        continue;
                    echo $relatedItemsListView->emitSubsectionHeader($indirectSubtree->getName());
                    foreach ($indirectSubtreeKids as $kid)
                    {
                        echo $relatedItemsListView->emitSubsection($kid);
                    }
                }
            }
            else
            {
                echo $relatedItemsListView->emitSection($directKid, $excludeItem);
            }
        }
        ?>
    </ul>
</div>

<script>
    jQuery(document).ready(function () {
        var showMoreText = [];
        jQuery('.related-items-show-more a').click(function() {
            var groupId = '#' + this.id;
            var itemClassName = '.' + this.id + '-extra';
            if (jQuery(itemClassName).css('display') === "none") {
                // Remember the 'Show more' message because it says how many more items to show.
                showMoreText[groupId] = jQuery(groupId).text();
                jQuery(groupId).text("<?php echo __('Show less'); ?>");
                jQuery(itemClassName).slideDown("fast");
            } else {
                // Restore the original 'Show more' message.
                jQuery(groupId).text(showMoreText[groupId]);
                jQuery(itemClassName).fadeOut("fast");
            }
            // Prevent the browser from moving to the top of the page as though you just linked to a new page.
            return false;
        });
    });
</script>
