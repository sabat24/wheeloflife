<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title;?></title>


<?php foreach($styles as $file => $type) { echo HTML::style($file, array('media' => $type)), "\n"; }?>
<?php foreach($scripts as $file) { echo HTML::script($file, NULL, TRUE), "\n"; }?>
<script type="text/javascript">
    var main_url = '<?= URL::site(FALSE, TRUE);?>';
    <?=javascript::render_vars()?>
</script>
</head>
<body>

<?php echo $content;?>

</body>
</html>