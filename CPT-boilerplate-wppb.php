<?php
// CPT and Meta - This goes in includes/class-[plugin-name].php
$this->loader->add_action( 'init', $plugin_admin, 'cpt_name' );
$this->loader->add_action( 'add_meta_boxes_cpt_name', $plugin_admin, 'cpt_names_add_meta' );
$this->loader->add_action( 'save_post', $plugin_admin, 'cpt_names_save_meta' );

// Admin Screen Columns for CPT - This goes in includes/class-[plugin-name].php
$this->loader->add_filter( 'manage_cpt_name_posts_columns', $plugin_admin, 'cpt_name_columns' );
$this->loader->add_filter( 'manage_edit-cpt_name_sortable_columns', $plugin_admin, 'cpt_name_sortable_columns' );
$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'cpt_name_orderby' );
$this->loader->add_action( 'manage_cpt_name_posts_custom_column', $plugin_admin, 'cpt_name_columns_content', 10, 2 );

// Everything below goes in admin/class-[plugin-name].php
public function cpt_name()
{
    $session_labels = array(
        'name' 			=> 'CPT Names',
        'singular_name'	=> 'CPT Name'
    );

    $session_settings = array(
        'labels'		=> $session_labels,
        'supports'		=> array( 'title' ),
        'public' 		=> true,
        'has_archive' 	=> true,
        //'menu_icon' 	=> 'dashicons-calendar-alt',
        'rewrite' 		=> array( 'slug' => 'cpt-name' ),
    );

    register_post_type( 'cpt_name', $session_settings );
}

public function cpt_name_columns( $columns )
{
    $columns = array(
        'cb'					=> 'Select',
        'title'                 => 'Title',
        'meta_name' 			=> 'Meta Name',
    );

    return $columns;
}

public function cpt_name_sortable_columns( $sortable )
{
    $sortable['meta_name']		= 'meta_name';

    return $sortable;
}

public function cpt_name_orderby( $query )
{
    if( ! is_admin() )
    {
        return;
    }

    $orderby = $query->get( 'orderby' );

    if( 'meta_name' == $orderby ) 
    {
        /*
        $query->set( 'meta_key','cpt_name_date' );
        $query->set( 'order', 'ASC' );
        $query->set( 'orderby','meta_value' );
        */
    }
}

public function cpt_name_columns_content( $column, $post_ID )
{
    switch ($column) 
    {
        case 'cb':
            echo '<input type="checkbox" disabled', ( is_sticky( $post_id ) ? ' checked' : ''), '/>';
            break;

        case 'meta_value':
            echo get_post_meta( $post_ID, 'cpt_name_meta_name', true );
            break;

        default:
            break;
    }
}

public function cpt_names_add_meta()
{
    add_meta_box(
        'cpt_name_meta_box',
        'Meta Box Name',
        array( $this, 'cpt_names_display_meta'),
        'cpt_name',
        'advanced',
        'high'
    );
}

public function cpt_names_display_meta( $post )
{
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cpt_names_save_meta', 'cpt_names_meta_nonce' );

    // Get the current data
    $meta_name 		= esc_attr( get_post_meta( $post->ID, 'cpt_name_date', true ) );


    // Input fields
    echo '<label class="kitchen-admin-label" for="cpt_name_meta_name_field">';
        echo 'Meta Name';
    echo '</label> ';
    echo '<input class="kitchen-admin-input" type="text" id="cpt_name_meta_name_field" 
        name="cpt_name_meta_name_field" value="' . $meta_name . '" size="100" />';
}

public function cpt_names_save_meta( $post_ID )
{
    // Check if our nonce is set.
    if ( ! isset( $_POST['cpt_names_meta_nonce'] ) ) 
    {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['cpt_names_meta_nonce'], 'cpt_names_save_meta' ) ) 
    {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
    {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) 
    {
        if ( ! current_user_can( 'edit_page', $post_ID ) ) 
        {
            return;
        }
    } 
    else 
    {

        if ( ! current_user_can( 'edit_post', $post_ID ) ) 
        {
            return;
        }
    }

    // Check for data
    if( ! isset( $_POST['cpt_name_meta_name_field'] ) )
    {
        return;
    }

    // Sanitize the fields
    $meta_name_field_value 		= sanitize_text_field( $_POST['cpt_name_meta_name_field'] );

    // Update the meta
    update_post_meta( $post_ID, 'cpt_name_meta_name', $meta_name_field_value );
}
