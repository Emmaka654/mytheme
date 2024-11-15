<?php get_header(); ?>

<div class="container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <div><?php the_content(); ?></div>
    <?php endwhile; else : ?>
        <p><?php esc_html_e('Sorry, no posts found.', 'mytheme'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
