<?php

/* Start Filter existing post types */

function cog_modify_custom_post_type_args($args, $post_type)
{
    if ($post_type === 'project') {
        $args['has_archive'] = 'projects'; // Set the archive slug
        $args['public'] = true;
        $args['publicly_queryable'] = true;
        $args['rewrite'] = array(
            'slug' => 'projects',
            'with_front' => false,
        );
    }
    return $args;
}
add_filter('register_post_type_args', 'cog_modify_custom_post_type_args', 99, 2);

// Add a function to replace the category placeholder in the permalink for projects
function cog_projects_post_type_link($post_link, $post)
{
    if ($post->post_type === 'project') {
        $terms = wp_get_object_terms($post->ID, 'project_category');
        if ($terms) {
            $category_slug = $terms[0]->slug;
            return home_url("projects/{$category_slug}/{$post->post_name}/");
        } else {
            return home_url("projects/uncategorised/{$post->post_name}/");
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'cog_projects_post_type_link', 10, 2);

// Modify the rewrite rule for projects
function custom_rewrite_rules_cog_projects_category()
{
    add_rewrite_rule(
        '^projects/([^/]+)/([^/]+)/?$',
        'index.php?project=$matches[2]&project_category=$matches[1]',
        'top'
    );

    // Add a rule for the project archive
    add_rewrite_rule(
        '^projects/?$',
        'index.php?post_type=project',
        'top'
    );
}
add_action('init', 'custom_rewrite_rules_cog_projects_category');

// Add custom query var for project category
function cog_add_custom_query_vars($vars)
{
    $vars[] = 'project_category';
    return $vars;
}
add_filter('query_vars', 'cog_add_custom_query_vars', 99);

// Modify single project query
function modify_single_project_query($query)
{
    
    if (!is_admin() && $query->is_main_query() && $query->is_singular('project')) {
        $category_slug = get_query_var('project_category');

        if ($category_slug) {
            $taxonomy = 'project_category';
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
add_action('pre_get_posts', 'modify_single_project_query', 99);

/* END Filter existing post types */
