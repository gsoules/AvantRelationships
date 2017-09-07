<?php
$edgeLabel = $showEdgeLabels ? "'label':'data(label)',": '';
?>
var <?php echo $id; ?> = cytoscape({
    container:document.getElementById('<?php echo $id; ?>'),
    autoungrabify:<?php echo $autoungrabify; ?>,
    userPanningEnabled:<?php echo $userPanningEnabled; ?>,
    wheelSensitivity:0.1,
    minZoom:0.20,
    maxZoom:4.00,
    style: [
        {selector:'node',
            style: {
                'label':'data(name)',
                'color':'#999',
                'font-size':'10px',
                'text-max-width':125,
                'text-wrap':'wrap',
                'text-valign':'bottom',
                'text-margin-y':6,
                'border-color':'#efefef',
                'shape':'roundrectangle',
                'height':80,
                'width':80,
                'background-color':'#fff',
                'border-width':'1px',
                'padding':'4px',
                'background-width-relative-to':'inner',
                'background-height-relative-to':'inner',
                'background-fit':'contain'
            }},

        {selector: ':parent',
            style: {
                'text-valign':'top',
                'border-color':'#fff',
            }},

        {selector:'.expander',
            style: {
                'label':'data(name)',
                'font-size':'10px',
                'color':'#fff',
                'text-max-width':55,
                'text-wrap':'wrap',
                'text-valign':'center',
                'text-margin-y':'0',
                'background-color':'#A35380',
                'shape':'ellipse',
                'height':25,
                'width':25
            }},

        {selector:'edge',
            style: {
                <?php echo $edgeLabel; ?>
                'font-size':'12px',
                'color':'#4d728d',
                'text-max-width':75,
                'text-wrap':'wrap',
                'width':3,
                'line-color':'#c2daec',
                'curve-style': 'bezier',
                'target-arrow-shape':'triangle',
                'target-arrow-color':'#c2daec',
            }},

        {selector:'.expanderEdge',
            style: {
                'width':2,
                'line-color':'#D6B4CC',
                'target-arrow-color':'#D6B4CC'
            }},

        {selector:'.root',
            style: {
                'border-width':2,
                'border-color':'#2D882D',
                'height':100,
                'width':100,
            }},

        {selector:'.cursorNode',
            style: {
                'border-width':1,
                'border-color':'#2D882D'
            }},

        {selector:'.moreNode',
            style: {
                'color':'#A35380',
                'font-size':'9px',
                'height':24,
                'text-max-width':80,
                'text-valign':'center',
                'text-margin-y':0,
                'border-width':'0px',
                'padding':'0px'
            }}
    ]
});
