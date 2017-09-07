# AvantRelationships (plugin for Omeka Classic)

> IMPORTANT: This plugin is being prepared and documented for sharing on GitHub, but please do not use it just yet. It should be ready by the end of September 2017. Thanks for your patience.

The AvantRelationships plugin visually displays real-world relationships between items in an Omeka database. When you view an item, the plugin displays thumbnails and titles of related items. It also displays a graphical visualization depicting the relationships among items. The user instantly sees how the item fits in with the rest of the collection and can easily discover and view related items.

## Demonstration Sites
The best way to understand what AvantRelationships does is to see it in action. Here are two sites:
* Southwest Harbor Public Library's [Digital Archive].
* [Basic Omeka site](http://swhplibrary.net/demo/relationships/) using only the AvantRelationships plugin and the Seasons theme.

Both sites use items and relationship data from the Southwest Harbor Public Library's Digital Archive, but the library site has a highly customized theme and utilizes many other plugins. The basic Omeka site is an out-of-the-box Omeka installation with only the AvantRelationships plugin installed. It best demonstrates just how much functionality the plugin provides without any additional customization.

## Dependencies
AvantRelationships depends on the following open source libraries which are included in the `views/shared/javascripts` folder.
Click the links below to see copyrights and licenses for each.

* [Cytoscape.js](http://js.cytoscape.org/) - Graph theory / network library for analysis and visualisation
* [cytoscape-panzoom](https://github.com/cytoscape/cytoscape.js-panzoom) - widget that lets the user pan and zoom about a Cytoscape.js graph
* [Dagre](https://github.com/cytoscape/cytoscape.js-dagre) DAG (directed acyclic graph) for Cytoscape.js
* [CoSE Bilkent](https://github.com/cytoscape/cytoscape.js-cose-bilkent) layout for Cytoscape.js
* [Magnific Popup](https://github.com/dimsemenov/Magnific-Popup/) lightbox for jQuery
## Installation

The AvantRelationships plugin requires that the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin be installed. AvantCommon contains common logic used by AvantRelationships and a few other plugins (AvantSearch and AvantElements) that have not yet been released.

1. Install the [AvantCommon](https://github.com/gsoules/AvantCommon) plugin.
2. Unzip the AvantRelationships-master file into your Omeka installation's plugin directory.
3. Rename the folder to AvantRelationships.
4. Activate the plugin from the Admin → Settings → Plugins page.
5. Configure the plugin or accept the defaults.

## Uninstalling
You can uninstall AvantRelationships in the usual way; however, by default, the uninstaller will not remove the database tables that store relationship information. This is to protect against accidental deletion of important data. To remove the tables, you must check the Delete Tables option on the Configure Plugin page, save the change, and then proceed with uninstalling the plugin.

## Usage
Once installed, AvantRelationships extends the Omeka admin and public user interfaces to provide the ability to add and display relationships. Specifically, the plugin:
* Adds a **Relationships** menu item in the left navigation panel
* Adds a **Relationships** tab on the Edit page
* Adds a **Cover Image** tab on the Edit page
* Displays **Item Relationships** below the item's metadata
* Displays a **Relationships Visualization** on the Show page for an item that has relationships

The following subsections explain each of these extensions.

### Defining Relationship Types and Rules

Before you can establish a relationship between two items, you must first create one or more relationship types. A relationship type indicates the nature of the relationship between two items. The types of relationships you create will be dictated by the kinds of items in your Omeka database. To get a sense of relationship types, view the [relationships types] used by the [Digital Archive].

Relationships can be either uni-directional or bi-directional. A bi-directional relationship reads the same in both directions. For example, John is *married to* Mary and Mary is *married to* John. A uni-direction relationship reads one way in the forward direction and another way in the reverse direction. For example, John is *buried at* Arlington and Arlington is the *burial place of* John. You distinguish between the two kinds of relationships by the source and target labels you choose for the relationship. You use the same source and target label for bi-directional relationships (e.g. *married to*) and different labels for uni-directional relationships (e.g. *buried at* for the source label and *burial place of* for the target label).

Note that you only create a *one* relationship type for a uni-directional relationship. If you look again at the Digital Archive [relationships types] you'll see that although there is one row for *buried at* and a separate row for *burial place of*, both rows have the same Id number 39. It works this way so that the use of relationships is intuitive to humans and the implementation is proper for the underlying relational database.

You can enforce proper use of a specific relationship by adding rules to the relationship type. For example, you can use rules to ensure that the *buried at* relationship is only allowed when a person is buried at a place or when a place is the burial site of a person. A rule like this protects against accidentally apply the relationship backward or with the wrong kind of items. Use of rules is optional, but highly recommended to ensure data integrity.

To add or edit relationship types and rules, click on the **Relationships** item in the left admin menu and then click the link to either 
**Edit Relationship Types** or **Edit Relationship Rules**.


#### Editing Relationship Types
##### Source and Target Rules
##### Source and Target Labels
##### Directives
##### Ancestry

#### Editing Relationship Rules
##### Determiner
##### Descriptions
##### Rule
#### Adding Relationships Between Items
While editing an item, click on the **Relationships** tab to add, update, or remove a relationship for the item being edited.

#### Implicit Relationships

### Cover Image Feature
While editing an item, click on the **Cover Image** tab to set or remove the cover image for an item. Use of the Cover Image feature is not necessary for 

### Related Item Thumbnails and Titles

### Relationships Visualization

#### Hook: show_relationships_visualization
*     <?php fire_plugin_hook('show_relationships_visualization', array('view' => $this, 'item' => $item)); ?>


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

* Created by [gsoules](https://github.com/gsoules) for the Southwest Harbor Public Library.
* Copyright George Soules, 2016-2017.
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information.

## Acknowledgments

* [John S. and James L. Knight Foundation](https://knightfoundation.org/) for funding development of this plugin.

[Digital Archive]: http://swhplibrary.net/archive
[relationships types]: http://swhplibrary.net/digitalarchive/relationships/browse

