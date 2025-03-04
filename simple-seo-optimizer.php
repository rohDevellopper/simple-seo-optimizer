<?php
/*
 * Plugin Name: Simple SEO Optimizer
 * Description: A simple plugin to optimize SEO for your WordPress site.
 * Version: 1.0.0
 * Author: Hamid Ezzaki
 * Author URI: https://siteweb.es
 * Text Domain: simple-seo-optimizer
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @category  Marketing & SEO
 * @package   Simple_SEO_Optimizer
 * @author    Hamid Ezzaki
 * @license   GPLv2 or later
 * @link      https://siteweb.es
 */

// Hook to add meta boxes for SEO fields
add_action('add_meta_boxes', 'simple_seo_plugin_add_meta_boxes');

// Function to add meta boxes
function simple_seo_plugin_add_meta_boxes() {
    add_meta_box(
        'simple_seo_meta',            // ID for the meta box
        'SEO Meta Information',       // Title for the meta box
        'simple_seo_plugin_meta_box', // Callback function to display the content
        ['post', 'page'],             // Where to display (post and page editors)
        'normal',                     // Context
        'high'                        // Priority
    );
}

// Callback function to display the meta fields in the meta box
function simple_seo_plugin_meta_box($post) {
    // Retrieve current meta values if available
    $meta_title = get_post_meta($post->ID, '_simple_seo_meta_title', true);
    $meta_description = get_post_meta($post->ID, '_simple_seo_meta_description', true);
    $meta_keywords = get_post_meta($post->ID, '_simple_seo_meta_keywords', true); // Get current keywords

    // Add a nonce field for security
    wp_nonce_field('simple_seo_meta_box_nonce', 'simple_seo_meta_box_nonce_field');

    // Display the form fields
    ?>
    <p>
        <label for="simple_seo_meta_title">Meta Title</label>
        <input type="text" id="simple_seo_meta_title" name="simple_seo_meta_title" value="<?php echo esc_attr($meta_title); ?>" class="widefat" />
    </p>
    <p>
        <label for="simple_seo_meta_description">Meta Description</label>
        <textarea id="simple_seo_meta_description" name="simple_seo_meta_description" rows="4" class="widefat"><?php echo esc_textarea($meta_description); ?></textarea>
    </p>
    <p>
        <label for="simple_seo_meta_keywords">Meta Keywords (Separate with commas)</label>
        <textarea id="simple_seo_meta_keywords" name="simple_seo_meta_keywords" rows="4" class="widefat"><?php echo esc_textarea($meta_keywords); ?></textarea>
    </p>
    <p><small>Example: keyword1, keyword2, keyword3</small></p>
    <?php
}


// Hook to save the custom meta fields when the post is saved
add_action('save_post', 'simple_seo_plugin_save_meta_fields');

// Function to save the meta fields
function simple_seo_plugin_save_meta_fields($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify nonce exists before using it
    if (!isset($_POST['simple_seo_meta_box_nonce_field'])) {
        return;
    }

    // Sanitize and unslash the nonce before verifying
    $nonce = sanitize_text_field(wp_unslash($_POST['simple_seo_meta_box_nonce_field']));

    if (!wp_verify_nonce($nonce, 'simple_seo_meta_box_nonce')) {
        return;
    }

    // Verify if the user has permission to edit this post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save or update the meta fields
    if (isset($_POST['simple_seo_meta_title'])) {
        update_post_meta($post_id, '_simple_seo_meta_title', sanitize_text_field(wp_unslash($_POST['simple_seo_meta_title'])));
    }
    if (isset($_POST['simple_seo_meta_description'])) {
        update_post_meta($post_id, '_simple_seo_meta_description', sanitize_textarea_field(wp_unslash($_POST['simple_seo_meta_description'])));
    }
    if (isset($_POST['simple_seo_meta_keywords'])) {
        $keywords = sanitize_textarea_field(wp_unslash($_POST['simple_seo_meta_keywords']));
        update_post_meta($post_id, '_simple_seo_meta_keywords', $keywords);
    }
}


// Hook to add meta tags to the front-end HTML head
add_action('wp_head', 'simple_seo_plugin_add_meta_tags');

// Function to output meta tags in the head section
function simple_seo_plugin_add_meta_tags() {
    if (is_singular()) {
        global $post;

        // Retrieve the meta data for the current post/page
        $meta_title = get_post_meta($post->ID, '_simple_seo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_simple_seo_meta_description', true);
        $meta_keywords = get_post_meta($post->ID, '_simple_seo_meta_keywords', true);

        // Set default meta values if none are provided
        if (empty($meta_title)) {
            $meta_title = get_the_title($post->ID); // Use the post/page title if no custom meta title is set
        }
        if (empty($meta_description)) {
            $meta_description = wp_strip_all_tags(get_the_excerpt($post->ID)); // Use the excerpt if no custom meta description is set
        }
        if (empty($meta_keywords)) {
            $meta_keywords = ''; // Leave blank or add custom logic for keywords
        }

        // Split the meta_keywords string into an array if it's not empty
        $keywords_array = array_map('trim', explode(',', $meta_keywords));
        $meta_keywords_output = implode(', ', $keywords_array);

        // Output meta tags for the page
        echo '<meta name="title" content="' . esc_attr($meta_title) . '">' . "\n";
        echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        echo '<meta name="keywords" content="' . esc_attr($meta_keywords_output) . '">' . "\n";
    }
}
?>
