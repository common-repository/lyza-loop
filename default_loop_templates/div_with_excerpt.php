<?php
    global $more;
    $more = 0;
?>
<div class="<?php echo $loop_css_class ?>" id="loop_summary_<?php the_ID(); ?>"><h3><a href="<?php the_permalink(); ?>" title="Read <?php the_title(); ?>"><?php the_title(); ?></a></h3> <small><?php the_time('M j'); ?></small><br />
    <div <?php post_class(); ?>><?php the_excerpt(); ?></div>
</div>
