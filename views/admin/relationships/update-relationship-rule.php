<?php

// This code is executed when an admin clicks the Add, Remove, Update, or Update Order button on the Edit Relationship Types page.

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 0)
    return;

$relationshipRulessEditor = new RelationshipRulesEditor();
echo $relationshipRulessEditor->performAction($action);