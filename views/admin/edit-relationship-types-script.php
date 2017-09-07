<script type="text/javascript">

    var updateOrderButton = null;
    var itemEditorUrl = '<?php echo url('/relationships/update/type'); ?>';

    function addNewItem()
    {
        var lastItem = jQuery('ul#relationship-items-list > li:last-child');
        var newItem = lastItem.clone();

        newItem.attr('id', 'new-item');
        newItem.find('.relationship-type-title').first().text('New');
        newItem.find('.relationship-type-title').last().text('');
        newItem.find('.relationship-item-count').text('0');
        newItem.find('.drawer-contents').show();

        // Convert the Update button into the Save button.
        var saveButton = newItem.find('.update-item-button');
        saveButton.text('<?php echo __('Save'); ?>');
        saveButton.removeClass('update-item-button');
        saveButton.addClass('save-item-button');
        saveButton.click(function (event)
        {
            saveNewItem();
        });

        // Convert the Remove button into the Cancel button.
        var cancelButton = newItem.find('.remove-item-button');
        cancelButton.text('<?php echo __('Cancel'); ?>');
        cancelButton.removeClass('remove-item-button');
        cancelButton.removeClass('no-remove');
        cancelButton.addClass('cancel-add-button');
        cancelButton.show();
        cancelButton.click(function (event)
        {
            jQuery('#new-item').remove();
            jQuery('.add-item-button').show();
        });

        // Empty the new item's controls and append the new item to the end of the list.
        var inputs = newItem.find('input, select');
        inputs.val('');
        lastItem.after(newItem);

        // Hide the Add button while the user is adding a new item.
        jQuery('.add-item-button').hide();

        initializeItems();
    }

    function afterRemoveItem(itemId)
    {
        jQuery('#' + itemId).remove();
    }

    function afterSaveNewItem(data)
    {
        var newItem = jQuery('#new-item');
        newItem.attr('id', data.itemId);
        newItem.find('.drawer-contents').hide();

        // Convert the Save button back into the Update button.
        var updateButton = newItem.find('.save-item-button');
        updateButton.text('<?php echo __('Update'); ?>');
        updateButton.removeClass('save-item-button');
        updateButton.addClass('update-item-button');

        // Convert the Cancel button back into the Remove button.
        var removeButton = newItem.find('.cancel-add-button');
        removeButton.text('<?php echo __('Remove'); ?>');
        removeButton.removeClass('cancel-add-button');
        removeButton.addClass('remove-item-button');

        // All the user to add another item.
        jQuery('.add-item-button').show();

        initializeItems();
    }

    function afterUpdateItemOrder()
    {
        updateOrderButton.slideUp(500);
        jQuery('#message-area').text('<?php echo __('Order has been updated'); ?>');
    }

    function afterUpdateItem(id)
    {
        var item = jQuery('#' + id);
        var itemValues = getItemValues(item);
        item.find('.relationship-type-title').first().text(itemValues.sourceName);
        item.find('.relationship-type-title').last().text(itemValues.targetName);
        item.find('.update-item-button').fadeTo(0, 1.0);
        item.find('.drawer-contents').slideUp();
    }

    function getItemValues(item)
    {
        var itemValues =
        {
            order:item.find('.order').text(),
            sourceName:item.find('.source-name').val(),
            targetName:item.find('.target-name').val(),
            sourceRuleId:item.find('.source-rule-id').val(),
            targetRuleId:item.find('.target-rule-id').val(),
            sourceLabel:item.find('.source-label').val(),
            targetLabel:item.find('.target-label').val(),
            directives:item.find('.directives').val(),
            ancestry:item.find('.ancestry').val()
        };

        return itemValues;
    }

    function initializeItems()
    {
        removeEventListeners();

        var drawerButtons = jQuery('.drawer');
        var updateButtons = jQuery('.update-item-button');
        var removeButtons = jQuery('.remove-item-button');

        drawerButtons.click(function (event)
        {
            event.preventDefault();
            jQuery(this).parent().next().toggle();
            jQuery(this).toggleClass('opened');
        });

        updateButtons.click(function (event)
        {
            updateItem(jQuery(this).parents('li').attr('id'));
        });

        removeButtons.click(function (event)
        {
            removeItem(jQuery(this).parents('li').attr('id'));
        });

        jQuery('.no-remove').hide();
    }

    function moveItem(item)
    {
        var startIndex = item.data("startindex") + 1;
        var newIndex = item.index() + 1;
        if (newIndex !== startIndex)
        {
            updateOrderButton.show();
            jQuery('#message-area').text('');
        }
    }

    function removeEventListeners()
    {
        var drawerButtons = jQuery('.drawer');
        var updateButtons = jQuery('.update-item-button');
        var removeButtons = jQuery('.remove-item-button');

        drawerButtons.off('click');
        updateButtons.off('click');
        removeButtons.off('click');
    }

    function removeItem(itemId)
    {
        if (!confirm('<?php echo __('Remove this relationship?'); ?>'))
            return;

        jQuery('#' + itemId).fadeTo(750, 0.20);

        jQuery.ajax(
            itemEditorUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelationshipTypesEditor::REMOVE_RELATIONSHIP_TYPE; ?>,
                    id: itemId
                },
                success: function (data)
                {
                    afterRemoveItem(itemId);
                },
                error: function (data)
                {
                    alert('AJAX Error on Remove: ' + data.statusText);
                }
            }
        );
    }

    function saveNewItem()
    {
        var position = jQuery('ul#relationship-items-list > li').length;
        var newItem = jQuery('#new-item');
        newItem.find('.order').val(position);

        var itemValues = getItemValues(newItem);

        if (!validateItemValues(itemValues))
            return;

        newItem.find('.relationship-type-title').first().text(itemValues.sourceName);
        newItem.find('.relationship-type-title').last().text(itemValues.targetName);

        jQuery.ajax(
            itemEditorUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelationshipTypesEditor::ADD_RELATIONSHIP_TYPE; ?>,
                    type:JSON.stringify(itemValues)
                },
                success: function (data) {
                    afterSaveNewItem(data);
                },
                error: function (data) {
                    alert('AJAX Error on Save: ' + data.statusText);
                }
            }
        );
    }

    function updateItemOrder()
    {
        var order = jQuery('ul#relationship-items-list > li')
            .map(function(i, e)
            {
                return e.id;
            })
            .get();

        jQuery.ajax(
            itemEditorUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelationshipTypesEditor::UPDATE_RELATIONSHIP_TYPE_ORDER; ?>,
                    order: order
                },
                success: function (data)
                {
                    afterUpdateItemOrder();
                },
                error: function (data)
                {
                    alert('AJAX Error on Update Order: ' + data.statusText);
                }
            }
        );
    }

    function updateItem(id)
    {
        var item = jQuery('#' + id);
        var itemValues = getItemValues(item);
        itemValues.id = id;

        if (!validateItemValues(itemValues))
            return;

        item.find('.update-item-button').fadeTo(500, 0.20);

        jQuery.ajax(
            itemEditorUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo RelationshipTypesEditor::UPDATE_RELATIONSHIP_TYPE; ?>,
                    type: JSON.stringify(itemValues)
                },
                success: function (data) {
                    afterUpdateItem(id);
                },
                error: function (data) {
                    alert('AJAX Error on Update: ' + data.statusText);
                }
            }
        );
    }

    function validateItemValues(itemValues)
    {
        if (itemValues.sourceName.trim().length === 0 || itemValues.targetName.trim().length === 0)
        {
            alert('<?php echo __('Source Name and Target Name must both be specified'); ?>');
            return false;
        }

        if (itemValues.sourceLabel.trim().length === 0 || itemValues.targetLabel.trim().length === 0)
        {
            alert('<?php echo __('Source Label and Target Label must both be specified'); ?>');
            return false;
        }

        return true;
    }

    jQuery(document).ready(function ()
    {
        jQuery('#relationship-items-list').sortable({
            listType: 'ul',
            handle: '.main_link',
            items: 'li',
            revert: 200,
            toleranceElement: '> div',
            placeholder: 'ui-sortable-highlight',
            forcePlaceholderSize: true,
            containment: 'document',

            start: function(event, ui)
            {
                jQuery(ui.item).data("startindex", ui.item.index());
            },
            stop: function(event, ui)
            {
                moveItem(ui.item);
            }
        });

        initializeItems();

        jQuery('.add-item-button').click(function (event)
        {
            addNewItem();
        });

        updateOrderButton = jQuery('.update-order-button');

        updateOrderButton.click(function ()
        {
           updateItemOrder();
        });

        updateOrderButton.hide();

    });
</script>
