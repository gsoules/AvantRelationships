<?php

class RelatedItemsTreeNode
{
    protected $data;
    protected $id;
    protected $kids = array();
    protected $name;
    protected $parent;
    protected $relatedItem;

    public function __construct($id, $name, $relatedItem = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent = null;
        $this->relatedItem = $relatedItem;
    }

    public function addKid(RelatedItemsTreeNode $node)
    {
        $this->kids[$node->getId()] = $node;
        $node->parent = $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFirstKid()
    {
        $firstKid = null;
        if ($this->getKidCount() > 0)
        {
            $keys = array_keys($this->kids);
            $firstKid = $this->kids[$keys[0]];
        }
        return $firstKid;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getKidCount()
    {
        return count($this->kids);
    }

    public function getKids()
    {
        return $this->kids;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRelatedItem()
    {
        return $this->relatedItem;
    }

    public function hasRelatedItem()
    {
        return !empty($this->relatedItem);
    }

    public function hasSubtrees()
    {
        foreach ($this->kids as $kid)
        {
            if (!$kid->isLeaf())
                return true;
        }
        return false;
    }

    public function isLeaf()
    {
        return count($this->kids) == 0;
    }

    public function moveToGrandparent()
    {
        $grandparent = $this->parent->parent;
        $this->removeFromParent();
        $grandparent->addKid($this);
    }

    public function removeFromParent()
    {
        $parent = $this->parent;
        unset($parent->kids[$this->getId()]);
        $this->parent = null;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
