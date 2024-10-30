<?php
/*
Plugin Name: Lyza Loop
Plugin URI: http://www.lyza.com/lyza-loop
Description: Handle custom loops with re-usable templates and useful variables. Easier custom loops for template developers. Go to <a href="options-general.php?page=lyza_loop/lyza_loop.php">Lyza Loop&rarr;Settings</a> to configure.
Version: 0.3
Author: Lyza D. Gardner
Author URI: http://www.lyza.com
*/
$my_lyza_loop = NULL;
class LyzaLoop
{  
    static $option_defaults = Array('lyza_loop_template_location'   => 'loop_templates',
                                    'lyza_loop_exclude_repeats'     => true,
                                    'lyza_loop_default_template'    => 'simple_UL',
                                    'lyza_loop_suppress_stickies'   => true);
    public $options;        /* Options (as stored in WP database) */
    public $seen_post_ids;  /* Keep track of posts we've seen before, for duplicate suppression, if desired */
    public $template_list;  /* Existing list of templates, both default and custom 
                               This is a multi-dimensional array. 
                               @see LyzaLoop::getTemplateList()
                            */
    
    function __construct()
    {
        $this->seen_post_ids  = Array();
        $this->options        = Array();
        /* Populate $this->options with either stored values in the DB or from defaults */
        foreach(self::$option_defaults AS $oname => $default)
            $this->options["$oname"] = get_option($oname, $default);
        $this->template_list = $this->getTemplateList();
    }
    
    /**
     * Retrieve an option (relevant to this plugin) from the WP database. 
     * Really probably overdesigned; no real reason not to use WP's inherent get_option()
     * except that this returns false for options that this object doesn't know/care about.
     *
     * @access public
     * @param string    $option_name
     * @return Mixed|Boolean false on fail 
     */
    public function getOption($option_name)
    {
        if(array_key_exists($option_name, $this->options))
            return $this->options[$option_name];
        else
            return false;
    }
    
    /**
     * Simply retrieve current array of Post IDs (ints) that have already been seen
     * in this request.
     * 
     * @access public
     * @return Array    of post IDs
     */
    public function getSeenIDs()
    {
        return $this->seen_post_ids;
    }
    
    /**
     * Add the IDs from an Array of post objects to the $this->seen_post_ids Array.
     * 
     * @access public
     * @param Array     $posts Array of WP Post objects
     * @return          true
     */
     
    public function addSeen($posts)
    {
        foreach($posts as $apost)
            $this->seen_post_ids[] = $apost->ID;
        return true;
    }
    
    /**
     * Looks to see if a supplied template string exists as a file.
     * 
     * @access public
     * @param string    $file -- Template name: filename without extension, e.g. 'my_template'
     * @return string|Boolean   Full file path if found; false if not
     */
    public function findTemplate($file)
    {
        $custom_path        = self::getCustomTemplatePath();
        $default_path       = self::getDefaultTemplatePath();
        $plausible_paths    = Array(
                                $custom_path . $file . '.php',
                                $custom_path . $file . '.PHP',
                                $default_path . $file . '.php'
                            );
        foreach($plausible_paths AS $how_about_here)
        {
            if(file_exists($how_about_here))
                return $how_about_here;
        }
        return false;
    }
    
    /**
     * Get the full path to custom templates, for internal use.
     * 
     * @access private
     * @return string
     */
    private static function getCustomTemplatePath()
    {
        $custom_path    = TEMPLATEPATH . '/' . get_option('lyza_loop_template_location', 
                                               self::$option_defaults['lyza_loop_template_location']);
        if(!preg_match('/\/$/', $custom_path))  /* Append slash, but only if needed */
            $custom_path .= '/'; 
        return $custom_path;     
    }

    /**
     * Get the full path to default templates, for internal use.
     * 
     * @access private
     * @return string
     */    
    private static function getDefaultTemplatePath()
    {
        $default_path   = dirname(__FILE__) . '/default_loop_templates/'; 
        return $default_path;     
    }

    /**
     * Utility formatting function; for building data about templates. 
     *
     * @access private
     * @param string    $file Template name: filename without extension, e.g. 'my_template'
     * @param Boolean   $custom optional - Is this concerning a custom template or a default?
     * @return Array|Boolean Associative array with template data or false on fail
     */
    private static function makeTemplateEntry($file, $custom=false)
    {
        if(stripos($file, 'php') !== false)
        {
            $template_info  = Array();
            $file_nick      = preg_replace('/\.php.*/i', '', $file);
            $file_pretty    = ucwords(str_replace("_", " ", $file_nick));
            $fullpath       = '';
            if($custom)
                $fullpath = self::getCustomTemplatePath() . $file;
            else
                $fullpath = self::getDefaultTemplatePath() . $file;
            $template_info = Array(
                'template'  => $file_nick,
                'file'      => $file,
                'path'      => $fullpath,
                'display'   => $file_pretty . " ($file)"
            );
            return $template_info;
        }  
        return false;
    }
    
    /**
     * Build multi-dimensional array with template information.
     * 
     * @access public
     * @return Array    of template data
     */
    public static function getTemplateList()
    {
        $templates      = Array();
        
        if ($handle = opendir(self::getCustomTemplatePath())) 
        {
            $custom_templates = Array();
            
            /* Iterate through files in the custom template directory */
            while (false !== ($file = readdir($handle)))
            {
                $template_info = self::makeTemplateEntry($file, true);
                if($template_info && !array_key_exists($template_info['template'], $custom_templates))
                    $custom_templates[$template_info['template']] = $template_info;
            }
            closedir($handle);
            ksort($custom_templates);
            
            $templates['custom_templates'] = $custom_templates;
        }
        if ($handle = opendir(self::getDefaultTemplatePath())) 
        {
            $default_templates = Array();
            
            /* Iterate through files in the default template directory */
            while (false !== ($file = readdir($handle)))
            {
                $template_info = self::makeTemplateEntry($file, false);
                if($template_info && !array_key_exists($template_info['template'], $default_templates))
                    $default_templates[$template_info['template']] = $template_info;
            }
            closedir($handle);
            ksort($default_templates);
            
            $templates['default_templates'] = $default_templates;
        }
        return $templates;
    }
    
    /**
     * Register options with WordPress for this plugin
     * 
     */
    public static function options_init()
    {
        foreach(self::$option_defaults AS $oname => $default)
            register_setting('lyza-loop-options', $oname);
    }
    
    /**
     * Create WordPress Admin area page with content in $this->options_form()
     */
    static function admin_menu()
    {
        add_options_page('Lyza Loop Options', 'Lyza Loop', 'administrator', __FILE__, Array('LyzaLoop', 'options_form'));
    }
    
    /**
     * Admin form
     */
    static function options_form()
    {
        $form_options       = Array();
        $template_location  = get_option('lyza_loop_template_location',  self::$option_defaults['lyza_loop_template_location']);
        $exclude_repeats    = get_option('lyza_loop_exclude_repeats',    self::$option_defaults['lyza_loop_exclude_repeats']);
        $default_template   = get_option('lyza_loop_default_template',   self::$option_defaults['lyza_loop_default_template']);
        $suppress_stickies  = get_option('lyza_loop_suppress_stickies',  self::$option_defaults['lyza_loop_suppress_stickies']);
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div><h2>Lyza Loop Options</h2>
            <form method="post" action="options.php">
                <?php settings_fields('lyza-loop-options'); ?>
                <table class="form-table">

                <tr>
                <tr valign="top"><th scope="row">Exclude Repeats<br /><em>(Duplicate Posts)</em></th>
                <td>
                        <p>By default, keep track of seen posts and suppress them from being used in subsequent Lyza Loops (in the same page request).</p>
                        <input type="radio" name="lyza_loop_exclude_repeats" value="1" <?php if($exclude_repeats): ?>checked="checked"<?php endif; ?>/>
                            <strong>Yes</strong>, suppress duplicate posts.<br />
                        <input type="radio" name="lyza_loop_exclude_repeats" value="0" <?php if(!$exclude_repeats): ?>checked="checked"<?php endif; ?> />
                            <strong>No</strong>, if I need to suppress duplicates, I'll do it by hand. <br />
                </td>
                </tr>

                <tr>
                <tr valign="top"><th scope="row">Exclude Sticky Posts<br /><em></th>
                <td>
                        <p>By WordPress default, sticky posts will be returned at the top of all returned posts, for every query. This can be annoying, especially in custom loops.</p>
                        <input type="radio" name="lyza_loop_suppress_stickies" value="1" <?php if($suppress_stickies): ?>checked="checked"<?php endif; ?>/>
                            <strong>Yes</strong>, suppress stickies. If I want sticky posts, I'll specifically ask for them. <small><em>(Pssst, <a href="http://codex.wordpress.org/Template_Tags/query_posts#Sticky_Post_Parameters" title="Sticky Post Parameters for query_posts">here's how</a>)</em></small><br />
                        <input type="radio" name="lyza_loop_suppress_stickies" value="0" <?php if(!$suppress_stickies): ?>checked="checked"<?php endif; ?> />
                            <strong>No</strong>, if I need to suppress stickies, I'll do it by hand. <br />
                </td>
                </tr>
                
                <tr>
                <th scope="row">Default Loop Template</th>
                <td><p>Use this loop template by default:</p>

                <select name="lyza_loop_default_template">

                <?php $all_templates = LyzaLoop::getTemplateList(); 
                    if(array_key_exists('custom_templates', $all_templates) && sizeof($all_templates['custom_templates'])):
                ?>
                    <optgroup label="Your Custom Loop Templates">
                    <?php
                        foreach($all_templates['custom_templates'] AS $file_nick => $file_stuff):
                    ?>
                        <option value="<?php echo $file_nick ?>" <?php if($file_nick == $default_template): ?>selected="selected"<?php endif; ?>>
                            <?php echo $file_stuff['display']; ?>
                        </option>
                    <?php endforeach; ?>
                    </optgroup>
                <?php else: ?>
                    <optgroup label="Custom Templates -- None Yet!"></optgroup>
                <?php
                endif; 
                    if(array_key_exists('default_templates', $all_templates) && sizeof($all_templates['default_templates'])):
                ?>
                    <optgroup label="Default Loop Templates">
                    <?php foreach($all_templates['default_templates'] as $file_nick => $file_stuff): ?>
                        <option value="<?php echo $file_nick ?>" <?php if($file_nick == $default_template): ?>selected="selected"<?php endif; ?>>
                            <?php echo $file_stuff['display']; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
                </select>
                </td>
                </tr>
                
                <tr><th colspan="2"><h3>Slightly More Advanced Options</h3></th></tr>
                
                <tr valign="top"><th scope="row">Custom Loop Template File Location</th>
                <td>
                <?php echo TEMPLATEPATH; ?>/ <input type="text" name="lyza_loop_template_location" value="<?php echo $template_location ?>" size="30" />
                </td>
                </tr>
                
                </table>
               
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
        
            </form>
        </div>
        <?php
    }
}

/**
 * To use when a loop is needed in a page. Use $args to call query_posts and then 
 * use the template indicated to render the posts. The template gets included once
 * per post.
 * 
 * @param Array $args               Wordpress-style arguments; passed on to query_posts 
 *                                  'template' => name of post template to use for posts
 * @return Array of WP $post objs   Matching posts, if you should need them.
 */

function lyza_loop($args='')
{
    global $my_lyza_loop;
    if(!$my_lyza_loop instanceof LyzaLoop)
        $my_lyza_loop = new LyzaLoop();
    global $wp_query;
    global $post;
    $post_template_dir          = get_option('lyza_loop_template_location', 'loop_templates');
    /* 'template'
           name of the PHP template file to use for rendering the matching posts. 
           It should be the name of file, without path and without '.php' extension.
           e.g. the default value 'default' is $post_template_dir/default.php
           Required unless 'use_template' => false
       'use_template' 
           Boolean: Should the matching posts be rendered with a template
           (true) or just returned (false)?
       'exclude_repeats' 
           Boolean: Should we display and/or return all matching posts (default/false)
           or only those that this function has not seen before in this
           page request (true)?
       'post__not_in'
           This defaults to an empty array simply because I want it to be typed as an array
           at all times. You can blithely ignore this for now.
       'suppress_stickies'
           Don't get sticky posts at all if this is set to true.
       'caller_get_posts'
           A somewhat confounding parameter that prevents sticky posts from showing up 
           at the top of every single query.
    */
    $defaults                   = Array('template'          => $my_lyza_loop->getOption('lyza_loop_default_template'),
                                        'use_template'      => true,
                                        'exclude_repeats'   => $my_lyza_loop->getOption('lyza_loop_exclude_repeats'),
                                        'post__not_in'      => Array(),
                                        'suppress_stickies' => $my_lyza_loop->getOption('lyza_loop_suppress_stickies'),
                                        'caller_get_posts'  => 1); 
 
    $args = wp_parse_args($args, $defaults);
    
    // Bring arguments into local scope, vars prefixed with $loop_
    extract($args, EXTR_PREFIX_ALL, 'loop');
    
    if($loop_exclude_repeats)   
        $args['post__not_in'] = array_merge($my_lyza_loop->getSeenIDs(), $loop_post__not_in);    
    if($loop_suppress_stickies)
        $args['post__not_in'] = array_merge($args['post__not_in'], get_option('sticky_posts'));
    
    // Preserve the current query object and the current global post before messing around.
    $temp_query = clone $wp_query;                                  
    $temp_post  = clone $post;
 
    $template_path = $my_lyza_loop->findTemplate($loop_template);
 
    if(!$template_path)
    {
        printf ('<p class="error">Sorry, the template you are trying to use ("%s")
            in %s() does not exist (%s).',
            $template_path, 
            __FUNCTION__, 
            __FILE__);
        return false;
    }
    /* Allow for display of posts in order passed in post__in array
       [as the 'orderby' arg doesn't seem to work consistently without giving it some help]
       If 'post__in' is in args and 'orderby' is set to 'none', just grab those posts,
       in the order provided in the 'post__in' array.
    */
    if($loop_orderby && $loop_orderby == 'none' && $loop_post__in)     
    {
        foreach($loop_post__in as $post_id)
            $loop_posts[] = get_post($post_id);
    }
    else
        $loop_posts = query_posts($args);
        
    $wp_query->in_the_loop = true;  // Tell the WP Query object that we're in a loop
 
    if($loop_use_template)  // The following logic is used when rendering to a template
    {
        /* Utility vars for the loop; in scope in included template */
        $loop_count             = 0; 
        $loop_odd               = false;
        $loop_even              = false;
        $loop_first             = true;
        $loop_last              = false;
        $loop_css_class         = '';                               // For convenience
        $loop_size = sizeof($loop_posts);
        $loop_owner = $temp_post;       /* The context from within this loop is called
                                           the global $post before we query */
     
        foreach($loop_posts as $post)
        {
            $loop_count += 1;
            ($loop_count % 2 == 0) ? $loop_even = true : $loop_even = false;
            ($loop_count % 2 == 1) ? $loop_odd  = true : $loop_odd  = false;
            ($loop_count == 1) ?     $loop_first = true : $loop_first = false;
            ($loop_count == $loop_size) ? $loop_last = true : $loop_last = false;
            ($loop_even) ? $loop_css_class = 'even' : $loop_css_class = 'odd';
            setup_postdata($post);
            include($template_path);
        }
    }
    $wp_query->in_the_loop = false;
    $wp_query   = clone $temp_query;    // Put the displaced query and post back into global scope
    $post       = clone $temp_post;     // And set up the post for use.
    setup_postdata($post);
    $my_lyza_loop->addSeen($loop_posts);
    return $loop_posts;
}

add_action('admin_menu', Array('LyzaLoop', 'admin_menu'));
add_action('admin_init', Array('LyzaLoop', 'options_init'));
?>