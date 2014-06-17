<ul>
    <?php
    foreach($sub_menu as $menu){
        $class = isset($menu['class']) ? array($menu['class']) : array();
        if ($menu['selected'] == true) {
            $class[] = 'current';
        }

        echo '<li><a href="'.$menu['href'].'"'.(count($class) > 0 ? ' class="'.implode(' ', $class).'"' : '').' title="'.$menu['title'].'"><span>'.$menu['title'].'</span></a></li>';
    }  
    
    if (!empty($hiddenMenu)) {
        echo '<li><a href="#" title="Rozwiń" class="more"><span>Więcej pozycji</span></a></li>';
    }
    ?>
</ul>