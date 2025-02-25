<?php get_header(); ?>

<div class="container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div><?php the_content(); ?></div>
    <?php endwhile; else : ?>
        <p><?php esc_html_e('Sorry, no pages found.', 'mytheme'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
