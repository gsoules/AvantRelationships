<div id="related-items-graph">
    <h2><?php echo __('Relationships'); ?></h2>
    <a class="cy-popup-link" href="#cyPopupBox"><?php echo __('Enlarge'); ?></a>

    <div class="cyPreviewBox">
        <div id="cyPreview" class="graph"></div>
    </div>

    <div id="cyPopupBox" class="cyPopupBox mfp-hide">
        <div id="cyPopup" class="graph"></div>
    </div>

    <script>
        <?php
        echo $this->partial('graph-elements.php', array(
            'id' => 'cyPreview',
            'autoungrabify' => 'true',
            'userPanningEnabled' => 'false',
            'showEdgeLabels' => false));

        echo $this->partial('graph-elements.php', array(
            'id' => 'cyPopup',
            'autoungrabify' => 'false',
            'userPanningEnabled' => 'true',
            'showEdgeLabels' => true));

        echo "$graphData\r";
        echo $this->partial('graph-script.js');
        ?>
    </script>
</div>
