<?php

$box = $this->getMenu("header");

?>

<div class="menu col-12">
    <?php
    foreach($box as $key=>$menu) {
    ?>
    <a href="<?php echo $menu['link']; ?>"><?php echo $menu['title']; ?></a>
    <?php
    }
    ?>
</div>