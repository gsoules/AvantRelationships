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
                echo $relatedItemsListView->emitSection($directKid, $itemId);

                $indirectSubtrees = $directKid->getKids();
                foreach ($indirectSubtrees as $indirectSubtree)
                {
                    $indirectSubtreeKids = $indirectSubtree->getKids();
                    if (count($indirectSubtreeKids) == 0)
                        continue;
                    echo $relatedItemsListView->emitSubsectionHeader($indirectSubtree->getName());
                    foreach ($indirectSubtreeKids as $kid)
                    {
                        echo $relatedItemsListView->emitSubsection($kid, $itemId);
                    }
                }
            }
            else
            {
                echo $relatedItemsListView->emitSection($directKid, $itemId, $excludeItem);
            }
        }
        ?>
    </ul>
</div>
