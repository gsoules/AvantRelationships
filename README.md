# AvantRelationships (plugin for Omeka Classic)

#################################################

> **This plugin is under development. Please wait for Release 2.0.**

#################################################

The AvantRelationships plugin visually displays real-world relationships between items in an Omeka database.
When you view an item, the plugin displays thumbnails and titles of related items. It also displays a graphical
visualization (shown below) depicting the relationships among items. The user instantly sees how the item fits
in with the rest of the collection and easily discovers related items.

![Example](http://swhplibrary.net/wp/wp-content/uploads/2017/09/view-relationships-2.jpg)

This plugin was developed for the [Southwest Harbor Public Library](http://www.swhplibrary.org/), in Southwest Harbor, Maine. Funding was provided in part by the [John S. and James L. Knight Foundation](https://knightfoundation.org/). The library's [Digital Archive] contains historic photographs, documents, maps, and research material given to and shared with the library since about 1900. The Archive illustrates the way people, houses, businesses, vessels, Acadia National Park, places, structures, organizations, and events relate to one another and tells the story of how islanders and “people from away” lived on and visited Mount Desert Island, Maine, in the nineteenth, twentieth, and twenty-first centuries.

## Demonstration Sites
The best way to understand what AvantRelationships does is to see some examples. Below are links to two different Omeka sites that display the same item. Both sites use items and relationship data from the Southwest Harbor Public Library's Digital Archive, but the library site has a highly customized theme and utilizes many other plugins. The basic Omeka site is an out-of-the-box Omeka installation with only the AvantRelationships plugin installed. The basic site makes it easy to see just the functionality the plugin provides.

* Southwest Harbor Public Library [Digital Archive site].
* A [basic Omeka site] using only the AvantRelationships plugin and the Seasons theme.

## Dependencies
AvantRelationships depends on the following open source libraries which are included in either the
`views/shared/javascripts` folder or in AvantCommon.
Click the links below to see copyrights and licenses for each.

* [Cytoscape.js](http://js.cytoscape.org/) - graph theory / network library for analysis and visualization
* [cytoscape-panzoom](https://github.com/cytoscape/cytoscape.js-panzoom) - widget that lets the user pan and zoom about a Cytoscape.js graph
* [Dagre](https://github.com/cytoscape/cytoscape.js-dagre) - DAG (directed acyclic graph) for Cytoscape.js
* [CoSE Bilkent](https://github.com/cytoscape/cytoscape.js-cose-bilkent) - layout for Cytoscape.js
* [AvantCommon](https://github.com/gsoules/AvantCommon) plugin (see Installation section below)

AvantRelationships requires that each item have a unique identifier. It uses the identifiers to establish the relationship
between two items. It assumes that you are using the Dublin Core Identifier element
for this purpose. If you are using another element, you must specify it on the AvantCommon configuration page. It also
assumes use of the Dublin Core Title element, but you can specify another element on the AvantCommon configuration page.

## Installation

The AvantRelationships plugin requires that the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin be installed.
AvantCommon contains common logic used by AvantRelationships and [AvantSearch](https://github.com/gsoules/AvantSearch).

To install the AvantRelationships plugin, follow these steps:

1. First install and activate the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin.
1. Configure the AvantCommon plugin to specify your item identifier and title elements.
1. Unzip the AvantRelationships-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantRelationships.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. Configure the AvantRelationships plugin or accept the defaults.


**Default Relationship Rules and Types**

To help get you started using AvantRelationships, the installer creates a small set of relationship types and rules.
After installation you can see and edit these by clicking on the **Relationships** menu item in the admin left
navigation panel. You'll need to
[edit/add/remove rules](http://swhplibrary.net/archive/relationship-rules/) to meet your own needs.
You also want to [edit/add/remove relationship types](http://swhplibrary.net/archive/relationship-types/) in ways that
make sense for your collection.

## Uninstalling
You can uninstall AvantRelationships in the usual way; however, by default, the uninstaller will not remove the database tables that store relationship information. This is to protect against accidental deletion of important data. To remove the tables, you must check the *Delete Tables* option on the Configure Plugin page, save the change, and then proceed with uninstalling the plugin.

## Usage
AvantRelationships has the following configuration options.

Option | Description
--------|------------
Visualization&nbsp;Preview |  Specify where the Relationships Visulization Preview should appear. You can have the visualization appear immediately after metadata elements,or you designate a location, e.g. in the sidebar, by calling the 'show_relationships_visualization' hook in your theme's `items/show.php` page. To not show the visualization, choose the `Don't show visualization option`.
Max Direct Items | Number of directly related items listed before displaying a "Show more" message.
MaxIndirectItems |  Number of indirectly related items listed before displaying a "Show more" message.
Implicit Relationships | Elements that have an implicit relationship to other items based on the Title of those items. See the description of this option below.
Custom&nbsp;Relationships | Callback functions that dynamically create custom relationships. See the description of this option below.
Delete Tables |  WARNING: Checking this option will cause all relationship database tables and data to be permanently deleted if you uninstall this plugin. Do not check this box unless you are certain that in the future you will not be using relationship data that you created (relationships, types, rules, and cover images) while using this plugin . If you are just experimenting with the plugin, leave the box unchecked. If you decide not to use the plugin, check the box, Save Changes, and then uninstall the plugin.


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

### Implicit Relationships
An implicit relationship is one where the value of an element for one item exactly matches the value of the Title
element for another item. For example, if the Creator element for a photograph item specifies the name of a
photographer and that photographer's name is used for the Title on another item, then there is an implicit
*Created / Created by* relationship between the items.
 
The AvantRelationships configuration page has an option called `Implicit Relationships` that lets you specify which
elements can have an implicit relationship to items that have a matching Title element value.

In the example above, the plugin displays implicit relationships in three ways:

* When someone is viewing one of the photograph items, its Creator text is shown as a hyperlink. Clicking this link
 takes you to the item having the photographer's name as its Title.

* When someone is viewing the item titled with the photographer's name, photographs created by that photographer
appear on that page as related items.

* The implicit relationships from the creator to his/her creations appear in the Visualization.

The syntax for each row of the Implicit Relationships option is

    <element-name> ":" <label>

Where:

* `<element-name>` is the name of an Omeka element.
* `<label>` specifies the text to describe the relationship in the direction from the titles item to the implicitly
related items. This text appears in the page's related items section and in the visualization.

###### Example:
```
Creator: Created
Publisher: Published
```

### Custom Relationships
This option lets you specify the names of custom callback functions that you write to dynamically create relationships
for the items being viewed. The function must create and array of one or more Item objects that are somehow related to
the item being viewed. These items will appear in their own relationship group at the end of the item's Show page
after all other relationship groups. A relationship to the group will also appear in the visualization.

Note that custom relationships are one-way from the item being viewed to other items. If you click one of the related
items, it's Show page will not display a relationship back to the original item.
 
Each row of the Custom Relationships option specifies one group. The synxtax is:

    <class-name> "," <function-name>

Where:

* `<class-name>` is the name of a PHP class in a custom plugin
* `<function-name>` is the name of a public static function in <class-name> 

###### Example use of option:
```
SomeCustomClass, createCustomRelationshps
```

###### Example custom callback function:

```
class SomeCustomClass
{
    public static function createCustomRelationshps(Item $item, RelatedItemsTree $tree)
    {
        $items = array();
        
        // Add code here to add items to the array.

        return $tree->createCustomRelationshipsGroup($items, 'Name of Relationship Group');
    }
}
```

#### Title Sync Option
The [AvantElements](https://github.com/gsoules/AvantElements) plugin has a
[Title Sync](https://github.com/gsoules/AvantElements#title-sync-option) option that makes it easy to keep
implicitly related items in sync with each other. If you change the title text in one item, Title Sync will
automatically update the corresponding text in implicitly related items.

Notes:
* The AvantRelationships plugin only detects an implicit relationships when there is an exact match between the element text in one
item and the corresponding Dublin Core Title text in another. If the text varies even by a space, the relationship won't be detected.
* When displaying a creator item, if there are a lot of creation items, the page will display a short list of creation items followed by a button
that the user can click to see all of the itmes. The number of items in the short list is controlled by the "Max indirect items" option
on the AvantRelationships configuration page. If AvantSearch is also installed and activated, clicking the button will display all of
the related creations as search results in an [Image View](http://swhplibrary.net/searching/search-results-image-view/). If AvantSearch is not active,
clicking the button will display all of the creation items inline on the creator item page.

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

If you don't want to show the preview, choose the *Don't show visualization* configuration option.

As examples, the [Digital Archive site] places the preview in the sidebar whereas the [Basic Omeka site] shows the preview in the default location below the metadata elements.

## Warning

Use it at your own risk.

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
