<script type="text/javascript">
    var updateRelationshipUrl = '<?php echo url('/relationships/update/relationship'); ?>';
    var detachedAddRelationshipRow = null;
    var relationshipNames = JSON.parse('<?php echo $relationshipNames; ?>');
    var primaryItemIdentifier = '<?php echo $primaryItemIdentifier; ?>';
    const RELATIONSHIPS_COOKIE = 'RELATIONSHIPS';

    function addActionButtonEventListeners()
    {
        var addButton = jQuery('.add-relationship-button');
        var editButtons = jQuery('.edit-relationship-button');
        var removeButtons = jQuery('.remove-relationship-button');
        var recentItemAdd = jQuery('.recent-item-add');
        var recentItemIdentifier = jQuery('.recent-item-identifier');
        var recentItemRemove = jQuery('.recent-item-remove');
        var recentItemThumbnail = jQuery('.recent-item-thumbnail');
        var recentItemsClearAll = jQuery('.recent-items-clear-all');

        addButton.click(function ()
        {
            // The single Add button used to manually add a relationship.
            addRelationship();
        });

        editButtons.click(function ()
        {
            editRelationship(jQuery(this).parents('tr'));
        });

        recentItemAdd.click(function ()
        {
            // The Add buttons that appear on each recent item row.
            copyDataToIdentifier(this);
            addRelationship();
        });

        recentItemIdentifier.click(function ()
        {
            copyDataToIdentifier(this);
        });

        recentItemRemove.click(function ()
        {
            var itemIdentifier = jQuery(this).attr('data-identifier');
            var itemId = jQuery(this).attr('data-id');
            var row = jQuery('#row-' + itemIdentifier);
            jQuery(row).hide("slow");
            removeItemFromCookie(itemId);
        });

        recentItemThumbnail.click(function ()
        {
            copyDataToIdentifier(this);
        });

        recentItemsClearAll.click(function ()
        {
            if (confirm('Clear all recently visited items?'))
            {
                removeAllItemsFromCookie();
                jQuery('#recent-items').remove();
                jQuery(this).remove();
            }
        });

        removeButtons.click(function ()
        {
            removeRelationship(jQuery(this).parents('tr'));
        });
    }

    function addActionLinkEventListeners()
    {
        var codeLinks = jQuery('.recent-relationship');

        codeLinks.click(function ()
        {
            var code = jQuery(this).attr('data-code');
            setSelectedRelationship(code);
            jQuery( "#related-item-identifier" ).focus();
        });
    }

    function addRelationship()
    {
        var relatedItemIdentifier = jQuery('#related-item-identifier').val();
        var code = jQuery('#relationship-type-code').val();
        var relationshipName = jQuery('#relationship-type-code option:selected').text();

        jQuery.ajax(
            updateRelationshipUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelatedItemsEditor::ADD_RELATIONSHIP; ?>,
                    primary: primaryItemIdentifier,
                    related: relatedItemIdentifier,
                    code: code
                },
                success: function (data)
                {
                    afterAddRelationship(data, relationshipName, relatedItemIdentifier, code);
                    saveSelectedRelationship(code);
                },
                error: function (data)
                {
                    alert('AJAX Error on Add: ' + data.statusText + ' : ' + data.responseText);
                }
            }
        );
    }

    function afterAddRelationship(data, relationshipName, relatedItemIdentifier, code)
    {
        if (data.success)
        {
            // Create a new Add row from the one the user just filled in and added.
            var addedRow = jQuery('.add-relationship-row');
            var newRow = addedRow.clone();

            // Empty the new row's controls and append the new row to the end of the table.
            var inputs = newRow.find('input, select');
            inputs.val('');
            addedRow.after(newRow);

            // Convert the newly added row into a read-only row and give it the Id of the newly added relationship.
            addedRow.removeClass('add-relationship-row');
            addedRow.attr('id', data.relationshipId);

            // Fill in the newly added row's columns.
            setRowColumnHtml(addedRow, relationshipName, relatedItemIdentifier, data.link);

            // Remove the newly added row's Add button and show its Edit and Remove buttons.
            var buttons = getRowButtonsColumn(addedRow);
            buttons.find('.add-relationship-button').remove();
            buttons.find('.edit-relationship-button').show();
            buttons.find('.remove-relationship-button').show();

            setActionButtonEventListeners();
            setSelectedRelationship(code);
        }
        else
        {
            displayErrorMessage(data.message);
        }
    }

    function afterRemoveRelationship(tr)
    {
        tr.remove();
    }

    function afterUpdateRelationship(data)
    {
        if (data.success)
        {
            // Get the new relationship and identifier values from the updated row.
            var updatedRelationshipName = jQuery('#relationship-type-code option:selected').text();
            var updatedRelationshipIdentifier = jQuery('#related-item-identifier').val();

            // Remove the original read-only relationship row.
            jQuery('.original-relationship-row').remove();

            // Convert the updated row into a read-only row and give it the Id of the updated relationship.
            // Note that the Id changes because the old relationship is removed from the relationships table
            // and the update is added to the table as a new relationship with a new Id.
            updatedRow = jQuery('.edit-relationship-row');
            updatedRow.removeClass('edit-relationship-row');
            updatedRow.attr('id', data.relationshipId);

            // Fill in the updated row's read-only columns.
            setRowColumnHtml(updatedRow, updatedRelationshipName, updatedRelationshipIdentifier, data.link);

            // Change the updated row's Update and Cancel buttons to Edit and Remove buttons.
            var buttons = getRowButtonsColumn(updatedRow);

            var editButton = buttons.find('.update-relationship-button');
            editButton.text('<?php echo __('Edit'); ?>');
            editButton.removeClass('update-relationship-button');
            editButton.addClass('edit-relationship-button');

            var removeButton = buttons.find('.cancel-relationship-button');
            removeButton.text('<?php echo __('Remove'); ?>');
            removeButton.removeClass('cancel-relationship-button');
            removeButton.addClass('remove-relationship-button');

            restoreNormalState();
        }
        else
        {
            displayErrorMessage(data.message);
        }
    }

    function cancelRelationshipUpdate()
    {
        jQuery('.edit-relationship-row').remove();
        jQuery('.original-relationship-row').show();
        restoreNormalState();
    }

    function copyDataToIdentifier(element)
    {
        var itemIdentifier = jQuery(element).attr('data-identifier');
        setItemIdentifier(itemIdentifier);
    }

    function displayErrorMessage(message)
    {
        jQuery.magnificPopup.open({
            items: {
                src: '<div class="relationship-editor-popup">' + message + '</div>',
                type: 'inline'
            }
        });
    }

    function editRelationship(readOnlyRow)
    {
        // Make a copy of the Add relationship row to use for the editable row since the Add row has the
        // needed Select and Input controls. Detach the Add row so it's not visible and so its Ids won't
        // conflict with the editable row Ids. Save off the Add row so it can be restored after editing.
        var addRelationshipRow = jQuery('.add-relationship-row');
        var editableRow = addRelationshipRow.clone();
        detachedAddRelationshipRow = addRelationshipRow.detach();

        // Convert the copied Add row into the Edit row.
        editableRow.removeClass('add-relationship-row');
        editableRow.addClass('edit-relationship-row');

        // The read-only row on which the user clicked the Edit button. Hide it for now. If the user click
        // the Cancel button, it will get shown again. If they click Update and the update is successful,
        // the read-only row will get removed after the update occurs.
        readOnlyRow.addClass('original-relationship-row');
        readOnlyRow.hide();

        // Disable the Edit and Remove buttons on all rows so the user's only options are Update and Cancel.
        jQuery(".action-button").fadeTo(0, .3);
        removeActionButtonEventListeners();

        // Get the relationship information from the read-only row.
        var relationshipName = getRowColumnText(readOnlyRow, 1);
        var relatedItemIdentifier = getRowColumnText(readOnlyRow, 2);
        var description = getRowColumnHtml(readOnlyRow, 3);

        // Insert the relationship information into the editable row.
        editableRow.find('input').val(relatedItemIdentifier);
        var td = editableRow.find('td');
        jQuery(td[2]).html(description);

        // Use the relationship name to set the selected element in the Select control
        var options = editableRow.find("select option");
        for (i = 0; i < options.length; i++)
        {
            var option = jQuery(options[i]);
            if (option.text() === relationshipName)
            {
                option.attr('selected', true);
                break;
            }
        }

        // Remove the Add button from the editable row.
        var buttons = getRowButtonsColumn(editableRow);
        var addButton = buttons.find('.add-relationship-button');
        addButton.remove();

        // Change the Edit button to the Update button.
        var updateButton = buttons.find('.edit-relationship-button');
        updateButton.text('<?php echo __('Update'); ?>');
        updateButton.removeClass('edit-relationship-button');
        updateButton.addClass('update-relationship-button');
        updateButton.show();
        updateButton.click(function ()
        {
            updateRelationship(readOnlyRow.attr('id'),);
        });

        // Change the Remove button to the Cancel button.
        var cancelButton = buttons.find('.remove-relationship-button');
        cancelButton.text('<?php echo __('Cancel'); ?>');
        cancelButton.removeClass('remove-relationship-button');
        cancelButton.addClass('cancel-relationship-button');
        cancelButton.show();
        cancelButton.click(function ()
        {
            cancelRelationshipUpdate();
        });

        // Insert the editable row after the read-only row.
        readOnlyRow.after(editableRow);
    }

    function getRowButtonsColumn(row)
    {
        var td = row.children();
        return jQuery(td[3]);
    }

    function getRowColumnHtml(tr, column)
    {
        return jQuery(tr.children('td:nth-child(' + column + ')')).html();
    }

    function getRowColumnText(tr, column)
    {
        return jQuery(tr.children('td:nth-child(' + column + ')')).text();
    }

    function initializeAddRowButtons()
    {
        var td = jQuery('tr.add-relationship-row > td');
        var actions = jQuery(td[3]);
        actions.find('.edit-relationship-button').hide();
        actions.find('.remove-relationship-button').hide();
    }

    function removeActionButtonEventListeners()
    {
        var addButtons = jQuery('.add-relationship-button');
        var editButtons = jQuery('.edit-relationship-button');
        var removeButtons = jQuery('.remove-relationship-button');

        addButtons.off('click');
        editButtons.off('click');
        removeButtons.off('click');
    }

    function removeRelationship(tr)
    {
        var relationshipId = tr.attr('id');
        var message = '<?php echo __('Remove "'); ?>' + getRowColumnText(tr, 1) + '<?php echo __('" relationship to item '); ?>' + getRowColumnText(tr, 2) + '<?php echo __('?'); ?>';

        if (!confirm(message))
            return;

        tr.fadeTo(750, 0.20);

        jQuery.ajax(
            updateRelationshipUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelatedItemsEditor::REMOVE_RELATIONSHIP; ?>,
                    id: relationshipId
                },
                success: function (data)
                {
                    if (data.success == true)
                        afterRemoveRelationship(tr);
                    else
                        alert('<?php echo __('Remove action failed'); ?>');
                },
                error: function (data)
                {
                    alert('AJAX Error on Remove: ' + data.statusText);
                }
            }
        );
    }

    function restoreNormalState()
    {
        // Reattach the Add new relationship row to the end of the table of relationships.
        jQuery('#relationships-metadata tr:last').after(detachedAddRelationshipRow);

        // Show the Add row and enable all action buttons.
        jQuery(".action-button").fadeTo(0, 1);
        initializeAddRowButtons();
        setActionButtonEventListeners();
    }

    function retrieveRelationshipCodes()
    {
        var value = Cookies.get(RELATIONSHIPS_COOKIE);
        var codes = [];
        if (value !== undefined)
        {
            codes = value.split(',');
        }

        return codes;
    }

    function saveSelectedRelationship(selectedCode)
    {
        var oldCodes = retrieveRelationshipCodes();
        var newCodes = '';
        if (oldCodes.length === 0)
        {
            newCodes = selectedCode;
        }
        else
        {

            // Put the selected code at index 0, and copy the old codes after it.
            newCodes = [selectedCode];
            var count = 1;

            for (code of oldCodes)
            {
                if (selectedCode === code)
                {
                    // The selected code was already in the stack. Ignore it since it's not on the top.
                    continue;
                }
                newCodes.push(code);
                count += 1;

                // Only show the last dozen selections.
                if (count >= 12)
                    break;
            }
            newCodes = newCodes.join(',');
        }

        Cookies.set(RELATIONSHIPS_COOKIE, newCodes, {expires: 14});

        showRecentRelationships();
    }

    function setActionButtonEventListeners()
    {
        removeActionButtonEventListeners();
        addActionButtonEventListeners();
    }

    function setDefaultRelationship()
    {
        codes = retrieveRelationshipCodes();
        if (codes.length > 0)
        {
            // Select the previously chosen relationship and put the cursor in the item number textbox.
            setSelectedRelationship(codes[0]);
        }
    }

    function setItemIdentifier(identifier)
    {
        jQuery('#related-item-identifier').val(identifier);
    }

    function setSelectedRelationship(code)
    {
        jQuery('#relationship-type-code option[value=' + code + ']').prop('selected', true);
    }

    function setRowColumnHtml(row, relationshipName, relatedItemIdentifier, descriptionLink)
    {
        var td = row.children();
        jQuery(td[0]).html(relationshipName);
        jQuery(td[1]).html(relatedItemIdentifier);
        jQuery(td[2]).html(descriptionLink);
    }

    function showRecentRelationships()
    {
        var recentRelationships =  jQuery('#recent-relationships');

        recentRelationships.empty();

        var name;
        codes = retrieveRelationshipCodes();
        for (code of codes)
        {
            name = relationshipNames[code];
            recentRelationships.append('<div class="recent-relationship" data-code="' + code + '">' + name + '</div>');
        }

        addActionLinkEventListeners();
    }

    function updateRelationship(oldRelationshipId)
    {
        var primaryItemIdentifier = '<?php echo $primaryItemIdentifier; ?>';
        var relatedItemIdentifier = jQuery('#related-item-identifier').val();
        var code = jQuery('#relationship-type-code').val();

        jQuery.ajax(
            updateRelationshipUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelatedItemsEditor::UPDATE_RELATIONSHIP; ?>,
                    primary: primaryItemIdentifier,
                    related: relatedItemIdentifier,
                    code: code,
                    id: oldRelationshipId
                },
                success: function (data, oldRelationshipId)
                {
                    afterUpdateRelationship(data);
                },
                error: function (data)
                {
                    alert('AJAX Error on Update: ' + data.statusText + ' : ' + data.responseText);
                }
            }
        );
    }

    jQuery(document).ready(function ()
    {
        initializeAddRowButtons();
        setActionButtonEventListeners();
        setDefaultRelationship();
        showRecentRelationships();
    });
</script>
