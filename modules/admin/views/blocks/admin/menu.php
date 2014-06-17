<ul class="group" id="menu_group_main">
	<?php
    $i=0;
    $items=count($main_menu);
    foreach ($main_menu as $menu) {
        if ($i==0) {
            $class='first';
        } elseif ($i == $items - 1) {
            $class='last';
        } else {
            $class='middle';
        }
            
        echo '<li class="item '.$class.'"><a href="'.$menu['url'].'"'.($menu['selected'] === true ? ' class="main current '.$menu['class'].'"':' class="'.$menu['class'].'"').' title="'.strip_tags($menu['title']).'"><span class="outer"><span class="inner '.$menu['class'].'">'.$menu['title'].'</span></span></a></li>';
        $i++;
    }
    ?>
</ul>