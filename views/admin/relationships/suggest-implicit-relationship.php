<?php

// This code is executed when an admin clicks the Add, Remove, or Edit button on the Relationships tab.

$action = isset($_POST['action']) ? $_POST['action'] : 0;
if ($action == 0)
    return;

$primaryItemIdentifier = isset($_POST['primary']) ? $_POST['primary'] : '';
$primaryItem = ItemView::getItemFromIdentifier($primaryItemIdentifier);
$relatedItemsModel = new RelatedItemsModel($primaryItem);
$relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $primaryItem);

echo $relatedItemsEditor->performAction($action);
