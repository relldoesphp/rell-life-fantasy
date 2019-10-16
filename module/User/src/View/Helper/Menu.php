<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/3/19
 * Time: 1:44 PM
 */

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Menu extends AbstractHelper
{
    // Menu items array.
    protected $items = [];

    // Active item's ID.
    protected $activeItemId = '';

    // Constructor.
    public function __construct($items=[])
    {
        $this->items = $items;
    }

    // Sets menu items.
    public function setItems($items)
    {
        $this->items = $items;
    }

    // Sets ID of the active items.
    public function setActiveItemId($activeItemId)
    {
        $this->activeItemId = $activeItemId;
    }

    // Renders the menu.
    public function render()
    {
        if (count($this->items)==0)
            return ''; // Do nothing if there are no items.
        $menuItems = "";
        // Render items
        foreach ($this->items as $item) {
            $menuItems = $this->renderItem($item);
        }

        $result = <<<EOT
<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav">
            {$menuItems}
        </ul>
    </div>
</nav>
EOT;


        return $result;
    }

    // Renders an item.
    protected function renderItem($item)
    {
        $id = isset($item['id']) ? $item['id'] : '';
        $isActive = ($id==$this->activeItemId);
        $label = isset($item['label']) ? $item['label'] : '';

        $result = '';

        if(isset($item['dropdown'])) {

            $dropdownItems = $item['dropdown'];

            $result .= '<li class="dropdown ' . ($isActive?'active':'') . '">';
            $result .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
            $result .= $label . ' <b class="caret"></b>';
            $result .= '</a>';

            $result .= '<ul class="dropdown-menu">';

            foreach ($dropdownItems as $item) {
                $link = isset($item['link']) ? $item['link'] : '#';
                $label = isset($item['label']) ? $item['label'] : '';

                $result .= '<li>';
                $result .= '<a href="'.$link.'">'.$label.'</a>';
                $result .= '</li>';
            }

            $result .= '</ul>';
            $result .= '</a>';
            $result .= '</li>';

        } else {
            $link = isset($item['link']) ? $item['link'] : '#';

            $result .= $isActive?'<li class="active">':'<li>';
            $result .= '<a href="'.$link.'">'.$label.'</a>';
            $result .= '</li>';
        }

        return $result;
    }
}