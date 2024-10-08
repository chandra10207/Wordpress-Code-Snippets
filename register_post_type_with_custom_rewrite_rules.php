<?php

/* START New post types */
// Register Custom Post Type
function register_custom_post_type_book() {
    $args = array(
        'label'  => 'Books',
        'public' => true,
        'publicly_queryable' => true,
        'rewrite' => array(
            'slug' => 'books',
            'with_front' => false,
        ),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions', 'custom-fields'),
        'has_archive' => 'books',
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-book-alt',
        'hierarchical' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'can_export' => true,
        'exclude_from_search' => false,
        'capability_type' => 'post',
    );
    register_post_type('book', $args);
}
add_action('init', 'register_custom_post_type_book');

// Add Divi Builder support for the book post type
function add_divi_support_to_book() {
    add_post_type_support('book', 'et-builder-layouts');
}
add_action('init', 'add_divi_support_to_book', 11);

// Enable Divi Builder for the book post type
function enable_divi_builder_for_book($post_types) {
    $post_types[] = 'book';
    return $post_types;
}
add_filter('et_builder_post_types', 'enable_divi_builder_for_book');

// Register Custom Taxonomy
function register_custom_taxonomy_book_category() {
    $args = array(
        'label' => 'Book Categories',
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book-category'), // Base slug
        'hierarchical' => true,
    );
    register_taxonomy('book_category', 'book', $args);
}
add_action('init', 'register_custom_taxonomy_book_category');

// Modify the book post type link
function book_post_type_link($post_link, $post) {
    if ($post->post_type === 'book') {
        $terms = wp_get_object_terms($post->ID, 'book_category');
        if ($terms) {
            $category_slug = $terms[0]->slug;
            return home_url("books/{$category_slug}/{$post->post_name}/");
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'book_post_type_link', 10, 2);

// Add custom rewrite rules for books
function custom_rewrite_rules_book_category() {
    add_rewrite_rule(
        '^books/([^/]+)/([^/]+)/?$',
        'index.php?book=$matches[2]&book_category=$matches[1]',
        'top'
    );
    
    // Add a rule for the book archive
    add_rewrite_rule(
        '^books/?$',
        'index.php?post_type=book',
        'top'
    );
}
add_action('init', 'custom_rewrite_rules_book_category');

// The rest of your code remains unchanged

function add_custom_query_vars_book_category($vars) {
    $vars[] = 'book_category';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars_book_category');


function modify_single_book_query($query) {

    if (!is_admin() && $query->is_main_query() && $query->is_singular('book')) {
        $category_slug = get_query_var('book_category');
        
        if ($category_slug) {
            $taxonomy = 'book_category';
            $term = get_term_by('slug', $category_slug, $taxonomy);
            
            if ($term) {
                // Check if the term exists and modify the query accordingly
                $query->set('tax_query', array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $category_slug,
                    ),
                ));
            }
        }
    }
}
add_action('pre_get_posts', 'modify_single_book_query');

/* END New post types */
