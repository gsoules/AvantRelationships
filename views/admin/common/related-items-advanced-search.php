<div class="field">
    <div class="two columns alpha">
        <?php echo $this->formLabel('related_items', __('Relationships')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Filter results where each item satisfies the criteria above AND:'); ?>
        </p>
        <p>
            <?php
            $option = @$_GET['relationship-option'];
            $choices = array(
                'has' => __('Has the selected relationship'),
                'not' => __('Does not have the selected relationship'),
                'any' => __('Has any relationship'),
                'none' => __('Has no relationships')
            );
            echo $this->formRadio('relationship-option', $option, null, $choices);
            ?>
            <br/>
        </p>
        <?php echo $this->formSelect('relationship-type-code', @$_GET['relationship-type-code'], array(), $formSelectRelationshipName); ?>
    </div>
</div>
