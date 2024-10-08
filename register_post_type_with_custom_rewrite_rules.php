<?php 

// Register Custom Post Type named Book and add book category slug
function register_custom_post_type_book() {
    $args = array(
        'label'  => 'Books',
        'public' => true,
        'publicly_queryable' => true,
        'rewrite' => array('slug' => 'books/%book_category%'), // Modified rewrite rule
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
    );
    register_post_type('book', $args);
}
add_action('init', 'register_custom_post_type_book');

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

// Add a function to replace the category placeholder in the permalink
function book_post_type_link($post_link, $post) {
    if ($post->post_type === 'book') {
        $terms = wp_get_object_terms($post->ID, 'book_category');
        if ($terms) {
            return str_replace('%book_category%', $terms[0]->slug, $post_link);
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'book_post_type_link', 10, 2);

// Modify the rewrite rule for books
function custom_rewrite_rules_book_category() {
    add_rewrite_rule(
        '^books/([^/]+)/([^/]+)/?$',
        'index.php?book=$matches[2]&book_category=$matches[1]',
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
