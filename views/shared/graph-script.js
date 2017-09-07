var expandedId = '';

function layoutGraph(graph, layoutId, animate)
{
    var layoutOptions = layouts[layoutMap[layoutId]];
    layoutOptions.animate = animate;
    layout = graph.layout(layoutOptions);
    layout.run();
}

function loadGraph(graph, datasetId, animate)
{
    var dataset = datasets[datasetId];
    for (var i = 0; i < dataset.length; i++)
    {
        graph.add(elements[dataset[i]]);
    }

    layoutGraph(graph, datasetId, animate);
}

function removeAllElements()
{
    var elements = cyPopup.$("*");
    for (var i = 0; i < elements.length; i++)
    {
        cyPopup.remove(elements[i]);
    }
}

function showRootGraph(graph, animate)
{
    expandedId = '';
    removeAllElements();
    loadGraph(graph, rootDatasetId, animate);
}

function showExpandedGraph(id)
{
    expandedId = id;
    removeAllElements();
    loadGraph(cyPopup, id, false);
}

cyPopup.on('tap', 'node', function ()
{
    if (this.hasClass('root'))
    {
        if (expandedId === '')
            jQuery.magnificPopup.close();
        else
            showRootGraph(cyPopup, false);
        return;
    }

    if (this.hasClass('expander'))
    {
        if (expandedId === this.id())
            showRootGraph(cyPopup, false);
        else
            showExpandedGraph(this.id());
    }
    else
    {
        var href = this.data('href');
        var ignoreTap = !href || href.length === 0;
        if (ignoreTap)
            return;
        try
        {
            window.location.href = href;
        }
        catch (e)
        {
        }
    }
});

cyPopup.on('mouseover', 'node', function (e)
{
    this.addClass('cursorNode');
});

cyPopup.on('mouseout', 'node', function (e)
{
    this.removeClass('cursorNode');
});

cyPreview.on('tap', function()
{
    jQuery('.cy-popup-link').click();
});

jQuery(document).ready(function ()
{
    showRootGraph(cyPreview, false);
    cyPreview.resize();
    cyPreview.fit();

    cyPopup.panzoom(
    {
        zoomOnly: true
    });

    jQuery('.cy-popup-link').magnificPopup(
        {
            type: 'inline',
            callbacks: {
                resize: function ()
                {
                    cyPopup.resize();
                    cyPopup.fit();
                },
                open: function ()
                {
                    showRootGraph(cyPopup, false);
                }
            }
        }
    );
});
