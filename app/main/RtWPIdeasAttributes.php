<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RtWPIdeasAttributes
 *
 * @author kaklo
 */
if (!class_exists('RtWPIdeasAttributes')) {

    class RtWPIdeasAttributes {
        
        /*
         * constructor 
         */

        public function __construct() {
            add_action('admin_init', array($this, 'save_attribute'), 1);
        }

        /*
         * show admin interface [rtwpideas attributes]
         */

        function attributes_page() {
            // Show admin interface
            if (!empty($_GET['edit']))
                $this->rtwpideas_edit_attribute();
            else
                $this->rtwpideas_add_attribute();
        }

        /*
         * perform action on attributes && hook-function for admin_init
         */

        function save_attribute() {
            $action_completed = $this->perform_action();
            // If an attribute was added, edited or deleted: clear cache and redirect
            if (!empty($action_completed)) {
                wp_redirect(admin_url('admin.php?p_type=' . $_GET['p_type'] . '&page=' . $_GET['page']));
            }
        }

        /*
         * register a Attributes[taxonomy] 
         */

        function register_taxonomy($post_type, $attr_id) {
            global $rtWPIdeasAttributesModel;
            $tax = $rtWPIdeasAttributesModel->get_attribute($attr_id);
            $name = rtwpideas_attribute_taxonomy_name($tax->attribute_name);
            // $pname=substr($name,3);
            $hierarchical = true;
            if ($name) {
                $label = ( isset($tax->attribute_label) && $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;
                $show_in_nav_menus = apply_filters('rtwpideas_attribute_show_in_nav_menus', false, $name);
                $labels = array(
                    'name' => $label,
                    'singular_name' => $label,
                    'search_items' => __('Search') . ' ' . $label,
                    'all_items' => __('All') . ' ' . $label,
                    'parent_item' => __('Parent') . ' ' . $label,
                    'parent_item_colon' => __('Parent') . ' ' . $label . ':',
                    'edit_item' => __('Edit') . ' ' . $label,
                    'update_item' => __('Update') . ' ' . $label,
                    'add_new_item' => __('Add New') . ' ' . $label,
                    'new_item_name' => __('New') . ' ' . $label
                );

                $args = array(
                    'hierarchical' => $hierarchical,
                    'labels' => $labels,
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'show_in_nav_menus' => $show_in_nav_menus,
                    'query_var' => true,
                    'rewrite' => array('slug' => $post_type . '/' . $name),
                    'with_front' => true
                );
                register_taxonomy($name, apply_filters('rtwpideas_taxonomy_objects_' . $name, $post_type), apply_filters('rtwpideas_taxonomy_args_' . $name, $args));
            }
        }

        /*
         * perform action [Add | Update | Delete ] on Attributes[taxonomy] 
         */

        function perform_action() {
            global $wpdb, $rtWPIdeasAttributesModel;
            $action_completed = false;
            // Action to perform: add, edit, delete or none
            $action = '';
            if (!empty($_POST['add_new_attribute'])) {
                $action = 'add';
            } elseif (!empty($_POST['save_attribute']) && !empty($_GET['edit'])) {
                $action = 'edit';
            } elseif (!empty($_GET['delete'])) {
                $action = 'delete';
            }

            // Add or edit an attribute
            if ('add' === $action || 'edit' === $action) {

                if ('edit' === $action) {
                    $attribute_id = absint($_GET['edit']);
                }
                // Grab the submitted data
                $attribute_label = ( isset($_POST['attribute_label']) ) ? (string) stripslashes($_POST['attribute_label']) : '';
                $attribute_name = ( isset($_POST['attribute_name']) ) ? rtwpideas_sanitize_taxonomy_name(stripslashes((string) $_POST['attribute_name'])) : '';
                $attribute_orderby = ( isset($_POST['attribute_orderby']) ) ? (string) stripslashes($_POST['attribute_orderby']) : '';
                $attribute_post_type = ( isset($_POST['p_type']) ) ? sanitize_title($_POST['p_type']) : 'wiki';

                // Auto-generate the label or slug if only one of both was provided
                if (!$attribute_label) {
                    $attribute_label = ucwords($attribute_name);
                } elseif (!$attribute_name) {
                    $attribute_name = rtwpideas_sanitize_taxonomy_name(stripslashes($attribute_label));
                }

                // Forbidden attribute names
                // http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
                $reserved_terms = array(
                    'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and',
                    'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day',
                    'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name',
                    'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm',
                    'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type',
                    'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence',
                    'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id',
                    'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year',
                );

                // Error checking
                if (!$attribute_name || !$attribute_name) {
                    $error = __('Please, provide an attribute name, slug, storage type and render type.');
                } elseif (strlen($attribute_name) >= 28) {
                    $error = sprintf(__('Slug “%s” is too long (28 characters max). Shorten it, please.'), sanitize_title($attribute_name));
                } elseif (in_array($attribute_name, $reserved_terms)) {
                    $error = sprintf(__('Slug “%s” is not allowed because it is a reserved term. Change it, please.'), sanitize_title($attribute_name));
                } else {
                    $taxonomy_exists = $rtWPIdeasAttributesModel->attribute_exists(rtwpideas_sanitize_taxonomy_name($attribute_name), $_GET['p_type']);

                    if ('add' === $action && $taxonomy_exists) {
                        $error = sprintf(__('Slug “%s” is already in use. Change it, please.'), sanitize_title($attribute_name));
                    }
                    if ('edit' === $action) {
                        $old_attribute_name = $rtWPIdeasAttributesModel->get_attribute_name($attribute_id);
                        if ($old_attribute_name != $attribute_name && rtwpideas_sanitize_taxonomy_name($old_attribute_name) != $attribute_name && $taxonomy_exists) {
                            $error = sprintf(__('Slug “%s” is already in use. Change it, please.'), sanitize_title($attribute_name));
                        }
                    }
                }

                // Show the error message if any
                if (!empty($error)) {
                    echo '<div id="rtwpideas_errors" class="error fade"><p>' . $error . '</p></div>';
                } else {

                    // Add new attribute
                    if ('add' === $action) {

                        $attribute = array(
                            'attribute_label' => $attribute_label,
                            'attribute_name' => $attribute_name,
                            'attribute_orderby' => $attribute_orderby,
                            'attribute_post_type' => $attribute_post_type
                        );

                        $rtWPIdeasAttributesModel->add_attribute($attribute);
                        
                        do_action('rtwpideas_attribute_added', $wpdb->insert_id, $attribute);

                        $action_completed = true;
                    }

                    // Edit existing attribute
                    if ('edit' === $action) {

                        $attribute = array(
                            'attribute_label' => $attribute_label,
                            'attribute_orderby' => $attribute_orderby
                        );

                        $rtWPIdeasAttributesModel->update_attribute($attribute, array('id' => $attribute_id));

                        do_action('rtwpideas_attribute_updated', $attribute_id, $attribute, $old_attribute_name);

                        if ($old_attribute_name != $attribute_name && !empty($old_attribute_name)) {
                            // Update taxonomies in the wp term taxonomy table
                            $wpdb->update(
                                    $wpdb->term_taxonomy, array('taxonomy' => rtwpideas_attribute_taxonomy_name($attribute_name)), array('taxonomy' => $old_attribute_name)
                            );

//							// Update taxonomy ordering term meta
//							$wpdb->update(
//								$wpdb->prefix . 'woocommerce_termmeta',
//								array( 'meta_key' => 'order_pa_' . sanitize_title( $attribute_name ) ),
//								array( 'meta_key' => 'order_pa_' . sanitize_title( $old_attribute_name ) )
//							);
//							// Update product attributes which use this taxonomy
//							$old_attribute_name_length = strlen( $old_attribute_name ) + 3;
//							$attribute_name_length = strlen( $attribute_name ) + 3;
//
//							$wpdb->query( "
//								UPDATE {$wpdb->postmeta}
//								SET meta_value = REPLACE( meta_value, 's:{$old_attribute_name_length}:\"pa_{$old_attribute_name}\"', 's:{$attribute_name_length}:\"pa_{$attribute_name}\"' )
//								WHERE meta_key = '_product_attributes'"
//							);
//							// Update variations which use this taxonomy
//							$wpdb->update(
//								$wpdb->postmeta,
//								array( 'meta_key' => 'attribute_pa_' . sanitize_title( $attribute_name ) ),
//								array( 'meta_key' => 'attribute_pa_' . sanitize_title( $old_attribute_name ) )
//							);
                        }

                        $action_completed = true;
                    }

                    //flush_rewrite_rules(true);
                }
            }

            // Delete an attribute
            if ('delete' === $action) {
                $attribute_id = absint($_GET['delete']);

                $attribute_name = $rtWPIdeasAttributesModel->get_attribute_name($attribute_id);

                if ($attribute_name && $rtWPIdeasAttributesModel->delete(array('id' => $attribute_id))) {

                    $taxonomy = rtwpideas_attribute_taxonomy_name($attribute_name);

                    if (taxonomy_exists($taxonomy)) {
                        $terms = get_terms($taxonomy, 'orderby=name&hide_empty=0');
                        foreach ($terms as $term) {
                            wp_delete_term($term->term_id, $taxonomy);
                        }
                    }

                    do_action('rtwpideas_attribute_deleted', $attribute_id, $attribute_name, $taxonomy);

                    $action_completed = true;
                }
            }

            return $action_completed;
        }

        /*
         * Shows the interface for editing new attributes
         */

        function rtwpideas_edit_attribute() {
            global $wpdb, $rtWPIdeasAttributesModel;

            $edit = absint($_GET['edit']);

            $attribute_to_edit = $rtWPIdeasAttributesModel->get_attribute($edit);

            $att_label = $attribute_to_edit->attribute_label;
            $att_orderby = $attribute_to_edit->attribute_orderby;
            $att_name = $attribute_to_edit->attribute_name;
            ?>
            <div class="wrap">
                <h2><i class="icon-tag"></i> <?php _e('Edit Attribute') ?></h2>
                <form action="admin.php?page=<?php echo $_GET['page']; ?>&amp;edit=<?php echo absint($edit); ?>&amp;p_type=<?php echo isset($_GET['p_type']) ? $_GET['p_type'] : ( ( isset($_GET['post_type']) ? $_GET['post_type'] : '' ) ) ?>" method="post">
                    <table class="form-table">
                        <tbody>
                            <tr class="form-field form-required">
                                <th scope="row" valign="top">
                                    <label for="attribute_label"><?php _e('Name'); ?></label>
                                </th>
                                <td>
                                    <input name="attribute_label" id="attribute_label" type="text" value="<?php echo esc_attr($att_label); ?>" />
                                    <p class="description"><?php _e('Name for the attribute (shown on the front-end).'); ?></p>
                                </td>
                            </tr>
                            <tr class="form-field form-required">
                                <th scope="row" valign="top">
                                    <label for="attribute_name"><?php _e('Slug'); ?></label>
                                </th>
                                <td>
                                    <span type="text"><?php echo esc_attr($att_name); ?></span>
                                    <p class="description"><?php _e('Unique slug/reference for the attribute; must be shorter than 28 characters.'); ?></p>
                                </td>
                            </tr>
                            <tr class="form-field form-required">
                                <th scope="row" valign="top">
                                    <label for="attribute_orderby"><?php _e('Default sort order'); ?></label>
                                </th>
                                <td>
                                    <select name="attribute_orderby" id="attribute_orderby">
                                        <option value="menu_order" <?php selected($att_orderby, 'menu_order'); ?>><?php _e('Custom ordering'); ?></option>
                                        <option value="name" <?php selected($att_orderby, 'name'); ?>><?php _e('Name'); ?></option>
                                        <option value="id" <?php selected($att_orderby, 'id'); ?>><?php _e('Term ID'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('Determines the sort order on the frontend for this attribute.'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type='hidden' name='p_type' value='<?php echo isset($_GET['p_type']) ? $_GET['p_type'] : ( ( isset($_GET['post_type']) ? $_GET['post_type'] : '' ) ); ?>' />
                    <p class="submit"><input type="submit" name="save_attribute" id="submit" class="button-primary" value="<?php _e('Update'); ?>"></p>
            <?php //nonce   ?>
                </form>
            </div>
            <?php
        }

        /*
         * Shows the interface for Adding new attributes
         */

        function rtwpideas_add_attribute() {
            global $rtWPIdeasAttributesModel;
            ?>
            <div class="wrap">
                <h2><i class="icon-tags"></i> <?php _e('Attributes'); ?></h2>
                <br class="clear" />
                <div id="col-container">
                    <div id="col-right">
                        <div class="col-wrap">
                            <table class="widefat fixed" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php _e('Name'); ?></th>
                                        <th scope="col"><?php _e('Slug'); ?></th>
                                        <th scope="col"><?php _e('Order by'); ?></th>
                                        <th scope="col" colspan="2"><?php _e('Terms'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php
            $post_type = ( isset($_GET['p_type']) ? $_GET['p_type'] : ( ( isset($_GET['post_type']) ? $_GET['post_type'] : 'post' ) ) );
            $attribute_taxonomies = $rtWPIdeasAttributesModel->get_all_attributes($post_type);
            if ($attribute_taxonomies) :
                foreach ($attribute_taxonomies as $tax) :
                    ?><tr>

                                                <td><a href="edit-tags.php?taxonomy=<?php echo esc_html(rtwpideas_attribute_taxonomy_name($tax->attribute_name)); ?>&amp;post_type=<?php echo $post_type; ?>"><?php echo esc_html($tax->attribute_label); ?></a>

                                                    <div class="row-actions"><span class="edit"><a href="<?php echo esc_url(add_query_arg('edit', $tax->id, 'admin.php?page=' . $_GET['page'] . '&amp;p_type=' . $post_type)); ?>"><?php _e('Edit'); ?></a> | </span><span class="delete"><a class="delete" href="<?php echo esc_url(add_query_arg('delete', $tax->id, 'admin.php?page=' . $_GET['page'] . '&amp;p_type=' . $post_type)); ?>"><?php _e('Delete'); ?></a></span></div>
                                                </td>
                                                <td><?php echo esc_html($tax->attribute_name); ?></td>
                                                <td><?php
                                                    switch ($tax->attribute_orderby) {
                                                        case 'name' :
                                                            _e('Name');
                                                            break;
                                                        case 'id' :
                                                            _e('Term ID');
                                                            break;
                                                        default:
                                                            _e('Custom ordering');
                                                            break;
                                                    }
                                                    ?></td>
                                                <td><?php
                                                    if (taxonomy_exists(rtwpideas_attribute_taxonomy_name($tax->attribute_name))) :
                                                        $terms_array = array();
                                                        $terms = get_terms(rtwpideas_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0');
                                                        if ($terms) :
                                                            foreach ($terms as $term) :
                                                                $terms_array[] = $term->name;
                                                            endforeach;
                                                            echo implode(', ', $terms_array);
                                                        else :
                                                            echo '<span class="na">&ndash;</span>';
                                                        endif;
                                                    else :
                                                        echo '<span class="na">&ndash;</span>';
                                                    endif;
                                                    ?></td>
                                                <td><a href="edit-tags.php?taxonomy=<?php echo rtwpideas_attribute_taxonomy_name($tax->attribute_name); ?>&amp;post_type=wiki" class="button alignright"><?php _e('Configure&nbsp;terms', 'woocommerce'); ?></a></td>
                                            </tr><?php
                    endforeach;
                else :
                    ?><tr><td colspan="6"><?php _e('No attributes currently exist.') ?></td></tr><?php
                endif;
                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <h3><?php _e('Add New Attribute') ?></h3>
                                <p><?php _e(''); ?></p>
                                <form action="admin.php?page=<?php echo $_GET['page'] ?>&amp;p_type=<?php echo $post_type; ?>" method="post">
                                    <div class="form-field">
                                        <label for="attribute_label"><?php _e('Name'); ?></label>
                                        <input name="attribute_label" id="attribute_label" type="text" value="" />
                                        <p class="description"><?php _e('Name for the attribute (shown on the front-end).'); ?></p>
                                    </div>

                                    <div class="form-field">
                                        <label for="attribute_name"><?php _e('Slug'); ?></label>
                                        <input name="attribute_name" id="attribute_name" type="text" value="" maxlength="28" />
                                        <p class="description"><?php _e('Unique slug/reference for the attribute; must be shorter than 28 characters.'); ?></p>
                                    </div>

                                    <div class="form-field">
                                        <label for="attribute_orderby"><?php _e('Default sort order'); ?></label>
                                        <select name="attribute_orderby" id="attribute_orderby">
                                            <option value="menu_order"><?php _e('Custom ordering'); ?></option>
                                            <option value="name"><?php _e('Name'); ?></option>
                                            <option value="id"><?php _e('Term ID'); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Determines the sort order on the frontend for this attribute.'); ?></p>
                                    </div>
                                    <input type='hidden' name='p_type' value='<?php echo $post_type; ?>' />
                                    <p class="submit"><input type="submit" name="add_new_attribute" id="submit" class="button" value="<?php _e('Add Attribute'); ?>"></p>
            <?php //nonce    ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    /* <![CDATA[ */

                    jQuery('a.delete').click(function() {
                        var answer = confirm("<?php _e('Are you sure you want to delete this attribute?'); ?>");
                        if (answer)
                            return true;
                        return false;
                    });

                    /* ]]> */
                </script>
            </div>
            <?php
        }

    }

}
