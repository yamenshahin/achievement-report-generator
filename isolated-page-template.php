<?php
/**
 * Isolated Page Template for Report Generator (Page ID 13630)
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="isolated-report-wrapper" style="padding: 20px;">
        <?php
        while (have_posts()):
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
    <?php wp_footer(); ?>
</body>

</html>