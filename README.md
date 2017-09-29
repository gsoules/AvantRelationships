# AvantRelationships (plugin for Omeka Classic)

The AvantRelationships plugin visually displays real-world relationships between items in an Omeka database. When you view an item, the plugin displays thumbnails and titles of related items. It also displays a graphical visualization (shown below) depicting the relationships among items. The user instantly sees how the item fits in with the rest of the collection and easily discovers related items.

![Example](http://swhplibrary.net/wp/wp-content/uploads/2017/09/view-relationships-2.jpg)

This plugin was developed for the [Southwest Harbor Public Library](http://www.swhplibrary.org/), in Southwest Harbor, Maine. Funding was provided in part by the [John S. and James L. Knight Foundation](https://knightfoundation.org/). The library's [Digital Archive] contains historic photographs, documents, maps, and research material given to and shared with the library since about 1900. The Archive illustrates the way people, houses, businesses, vessels, Acadia National Park, places, structures, organizations, and events relate to one another and tells the story of how islanders and “people from away” lived on and visited Mount Desert Island, Maine, in the nineteenth, twentieth, and twenty-first centuries.

## Demonstration Sites
The best way to understand what AvantRelationships does is to see some examples. Below are links to two different Omeka sites that display the same item. Both sites use items and relationship data from the Southwest Harbor Public Library's Digital Archive, but the library site has a highly customized theme and utilizes many other plugins. The basic Omeka site is an out-of-the-box Omeka installation with only the AvantRelationships plugin installed. The basic site makes it easy to see just the functionality the plugin provides.

* Southwest Harbor Public Library [Digital Archive site].
* A [basic Omeka site] using only the AvantRelationships plugin and the Seasons theme.


## Dependencies
AvantRelationships depends on the following open source libraries which are included in the `views/shared/javascripts` folder.
Click the links below to see copyrights and licenses for each.

* [Cytoscape.js](http://js.cytoscape.org/) - graph theory / network library for analysis and visualization
* [cytoscape-panzoom](https://github.com/cytoscape/cytoscape.js-panzoom) - widget that lets the user pan and zoom about a Cytoscape.js graph
* [Dagre](https://github.com/cytoscape/cytoscape.js-dagre) - DAG (directed acyclic graph) for Cytoscape.js
* [CoSE Bilkent](https://github.com/cytoscape/cytoscape.js-cose-bilkent) - layout for Cytoscape.js
* [Magnific Popup](https://github.com/dimsemenov/Magnific-Popup/) - lightbox for jQuery
* [AvantCommon](https://github.com/gsoules/AvantCommon) plugin (see Installation section below)

## Installation

The AvantRelationships plugin requires that the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin be installed. AvantCommon contains common logic used by AvantRelationships and [AvantSearch](https://github.com/gsoules/AvantSearch).

To install the AvantRelationships plugin, follow these steps:

1. Install the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin.
2. Unzip the AvantRelationships-master file into your Omeka installation's plugin directory.
3. Rename the folder to AvantRelationships.
4. Activate the plugin from the Admin → Settings → Plugins page.
5. Configure the plugin or accept the defaults.

**Default Relationship Rules and Types**

To help get you started using AvantRelationships, the installer creates the relationship types and rules shown below. After installation, you can see and edit these by clicking on the **Relationships** menu item in the admin left navigation panel. Unless you are starting with a new Omeka database, chances are that your items won't have types like 'Article' and 'Image' or subjects like 'People' or 'Structures' and so you'll need to [edit/add/remove rules](http://swhplibrary.net/archive/relationship-rules/) to match your own types and subjects. You also want to [edit/add/remove relationship types](http://swhplibrary.net/archive/relationship-types/) in ways that make sense with your collection.

![Relationships](http://swhplibrary.net/wp/wp-content/uploads/2017/09/Git-Hub-README-Relationship-Types-and-Rules.jpg)

## Uninstalling
You can uninstall AvantRelationships in the usual way; however, by default, the uninstaller will not remove the database tables that store relationship information. This is to protect against accidental deletion of important data. To remove the tables, you must check the *Delete Tables* option on the Configure Plugin page, save the change, and then proceed with uninstalling the plugin.

## Usage
Once installed, AvantRelationships extends the Omeka admin and public user interfaces to provide the ability to add and display relationships. Specifically, the plugin:
* Adds a **Relationships** menu item in the admin left navigation panel.
* Adds a **Relationships** tab on the admin item Edit page.
* Adds a **Cover Image** tab on the admin item Edit page.
* Displays **Item Relationship Groups** below the item's metadata on the public and admin Show pages.
* Displays a **Visualization Preview** on the public and admin Show pages for an item that has relationships.
* Adds **Relationships Filtering Options** to the bottom of the admin Advanced Search page.
* Inserts a small number of default relationship types into the relationship_types table.

To learn about features provided by AvantRelationships, see the following topics on the [Digital Archive](http://swhplibrary.net/archive/relationships/) website:
* [Archive Relational Model]
* [Relationships Overview]
* [Viewing Relationships]
* [Adding Relationships]
* [Implicit Relationships]
* [Cover Images]
* [Relationship Types]
* [Relationship Rules]

### Placement of the Visualization Graph Preview
The preview is a small image of the visualization graph. When you click on the preview's *Enlarge* link, a full size visualization appears in a popup. By default, the AvantRelationships plugin displays the preview immediately after an item's metadata elements and before item relationship groups. You can have the  preview appear somewhere else such as in the sidebar. To display the graph at a designated location:
1. On the Configure Plugin page for AvantRelationships, choose *At designated location* for the *Visualization Preview* option.
2. Call the hook shown below from `/themes/<your-theme-name>/items/show.php` as shown in the example below. 

```
<div id="secondary">
    <?php fire_plugin_hook('show_relationships_visualization', array('view' => $this, 'item' => $item)); ?>
    ...
</div><!-- end secondary -->
 ```

If you don't want to show the preview, choose the *At designated location* configuration option, but don't call the hook.

As examples, the [Digital Archive site] places the preview in the sidebar whereas the [Basic Omeka site] shows the preview in the default location below the metadata elements.

### Custom Relationships
You can add custom relationships using the `custom_relationships` filter which is called from `RelatedItemsTree::insertCustomRelationships()`. The filter allows you to insert your own `RelatedItemsTreeNode` objects into the `RelatedItemsTree` to be displayed to the user as described in the Digital Archive topic [Viewing Relationships].

Below is an example of a callback function that uses the filter. It calls a method (not shown) called `createCustomRelationshipsFor()` which returns a single `RelatedItemsTreeNode` object. The function implements the [Implicit Relationships] feature of the [Digital Archive]. Note that the last two parameters of `createCustomRelationshipsFor()` are the source and target labels for custom Creator and Publisher relationships types. See the Digital Archive topic [Relationship Types] for information about source and target labels.

```
public function filterCustomRelationships($nodes, $args)
{
    $item = $args['item'];
    $tree = $args['tree'];

    $node = $this->createCustomRelationshipsFor($item, $tree, 'Creator', 'Created');
    if (!empty($node))
        $nodes[] = $node;

    $node = $this->createCustomRelationshipsFor($item, $tree, 'Publisher', 'Published');
    if (!empty($node))
        $nodes[] = $node;

    return $nodes;
}
```

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Created by [gsoules](https://github.com/gsoules) for the Southwest Harbor Public Library
* Copyright George Soules, 2016-2017
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information


[Digital Archive]: http://swhplibrary.net/archive
[Digital Archive site]: http://swhplibrary.net/digitalarchive/items/show/9165
[Basic Omeka site]: http://swhplibrary.net/demo/relationships/items/show/9165
[relationships types]: http://swhplibrary.net/digitalarchive/relationships/browse
[Relationships Overview]: http://swhplibrary.net/archive/relationships/
[Viewing Relationships]: http://swhplibrary.net/archive/viewing-relationships/
[Adding Relationships]: http://swhplibrary.net/archive/adding-relationships/
[Implicit Relationships]: http://swhplibrary.net/archive/implicit-relationships/
[Cover Images]: http://swhplibrary.net/archive/cover-images/
[Relationship Types]: http://swhplibrary.net/archive/relationship-types/
[Relationship Rules]: http://swhplibrary.net/archive/relationship-rules/
[Archive Relational Model]: http://swhplibrary.net/archive/digital-relational-model/
