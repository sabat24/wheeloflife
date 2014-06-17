<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo $title.' - '.$head_title;?></title>
        <meta name="description" content="<?php echo $description;?>">
        <meta name="viewport" content="width=device-width">

        
        <style>
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
        
        
        <?php foreach ($styles as $file => $type) echo HTML::style($file, array('media' => $type)), "\n" ?>
        
        <!--[if lt IE 9]>
            <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
            <script>window.html5 || document.write('<script src="assets/default/js/vendor/html5shiv.js"><\/script>')</script>
        <![endif]-->
    </head>
    <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><?php echo __('Wheel of Life');?></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <?php
            foreach ($menu as $controller) {
                foreach ($controller as $action) {
                    foreach ($action as $item) {
                        $class = array($item['class']);
                        if ($item['selected']) {
                            $class[] = 'active';
                        }
                        
                        ?><li class="<?php echo implode(' ', $class);?>"><a href="<?php echo $item['url'];?>" title="<?php echo $item['title'];?>"><?php echo $item['title'];?></a></li><?php
                    }
                }
            } ?>
          </ul>
          
          <form id="login" class="<?php if ($user['u_id'] != -1) { echo 'hidden ';}?>ajax navbar-form navbar-right" action="<?php echo URL::site(Route::get('admin/auth')->uri(array('action' => 'login2')))?>">
            <div class="form-group">
              <input type="text" placeholder="<?php echo __('E-mail');?>" class="form-control" name="username" />
            </div>
            <div class="form-group">
              <input type="password" placeholder="<?php echo __('Password');?>" class="form-control" name="password" />
            </div>
            <button type="submit" class="btn btn-success"><?php echo __('Sign in');?></button>
          </form>
          <form id="loggedin" class="<?php if ($user['u_id'] == -1) { echo 'hidden ';}?>ajax navbar-form navbar-right" action="<?php echo URL::site(Route::get('admin/auth')->uri(array('action' => 'logout')))?>">
            <button type="submit" class="btn btn-success"><?php echo __('Log out');?></button>
          </form>
        </div><!--/.navbar-collapse -->
      </div>
    </div>

    
    <div class="jumbotron">
      <div class="container">
        <h1>Wheel of Life</h1>
        <p>Some informations about the project</p>
        <p><a class="btn btn-primary btn-lg" href="<?php echo URL::site(Route::get('static')->uri(array('action' => 'about')))?>">Learn more &raquo;</a></p>
      </div>
    </div>

    <div class="container">
      <?php echo $content;?>
      

      <hr>

      <footer>
        <p>&copy; <?php echo date('Y');?></p>
      </footer>
    </div> <!-- /container -->
        <?php foreach ($scripts as $file) echo HTML::script($file, NULL, TRUE), "\n" ?>
        <script>
        var main_url = '<?= URL::site(FALSE, TRUE);?>';
        <?=javascript::render_vars()?>
        <?=javascript::render_header_js()?>
        <?=javascript::render_jquery_ready()?>
        </script>
    </body>
</html>
