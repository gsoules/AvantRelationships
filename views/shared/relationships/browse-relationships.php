<?php
$pageTitle = __('Relationships');
echo head(array('title' => $pageTitle, 'bodyclass' => 'browse-relationships'));

$db = get_db();
$list = $db->getTable('RelationshipTypes')->getRelationshipTypesAndRules();

if (is_admin_theme())
{
    ?>
    <a class="button small green"
       href="<?php echo html_escape(url('relationships/edit/types')); ?>"><?php echo __('Edit Relationship Types'); ?></a>
    <a class="button small green"
       href="<?php echo html_escape(url('relationships/edit/rules')); ?>"><?php echo __('Edit Relationship Rules'); ?></a>
    <?php
}
?>

<?php echo "<h4>Relationship Types & Rules</h4>"; ?>

<table id="relationships-table">
    <tr>
        <td>Id</td>
        <td><?php echo __('This Item Rule'); ?></td>
        <td><?php echo __('Relationship Type'); ?></td>
        <td><?php echo __('Related Item Rule'); ?></td>
    </tr>
<?php
foreach ($list as $type)
{
    echo '<tr>';
    echo "<td>{$type[0]}</td>";
    echo "<td>{$type[1]}</td>";
    echo "<td>{$type[2]}</td>";
    echo "<td>{$type[3]}</td>";
    echo '</tr>';
}
?>
</table>

<?php
if (is_admin_theme())
{
    ?>
    <a id="validate-button" class="button small green"
       href="<?php echo html_escape(url('relationships/validate')); ?>"><?php echo __('Validate Relationships'); ?></a>
    <?php
}
?>

<script type="text/javascript">
    jQuery(document).ready(function ()
    {
        jQuery('#validate-button').click(function ()
        {
            if (confirm('<?php echo __('Validation can take several minutes. Click OK to continue.'); ?>'))
            {
                jQuery('.button').slideUp();
                jQuery('h4').text('<?php echo __('Validating Relationships. Please wait...'); ?>');
            }
            else
            {
                return false;
            }
        });
    });
</script>

<?php echo foot(); ?>
