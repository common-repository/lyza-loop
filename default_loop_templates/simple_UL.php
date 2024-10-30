<?php if($loop_first): ?>
    <ul>
<?php endif; ?>
    <li id="post_link_<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php the_time('M j'); ?> 
        <a href="<?php the_permalink(); ?>" title="Read <?php the_title(); ?>"><?php the_title(); ?></a>
    </li>
<?php if($loop_last): ?>
    </ul>
<?php endif; ?>