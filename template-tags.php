<?php

/**
 * Templating Class instance
 * @return string Template class
 */
function eab_get_template_class(): string {
    $class = apply_filters('eab-templating-template_class', 'Eab_Template');
    return class_exists($class) ? $class : 'Eab_Template';
}

/**
 * Template class caller wrapper.
 * A simple wrapper.
 * @param string $method Method to call
 * @param mixed ...$args Arguments to pass to the method
 * @return mixed|false Result of the call or false on failure
 */
function eab_call_template(string $method, ...$args) {
    $class_name = eab_get_template_class();
    
    if (!class_exists($class_name)) { 
        return false;
    }

    $callback = [$class_name, $method];

    if (!is_callable($callback)) {
        return false;
    }

    return call_user_func_array($callback, $args);
}

/**
 * Template method checker.
 * @param string $method Method to check
 * @return bool Exists or not.
 */
function eab_has_template_method(string $method): bool {
    if (!$method) { 
        return false;
    }

    $class_name = eab_get_template_class();

    if (!class_exists($class_name)) {
        return false;
    }

    return is_callable([$class_name, $method]);
}

/**
 * Get current time.
 * @return int Current timestamp.
 */
function eab_current_time(): int {
    return current_time('timestamp');
}

/* ----- PI compatibility layer ----- */

/**
 * Check if Post Indexer exists.
 * @return bool
 */
function eab_has_post_indexer(): bool {
    return class_exists('postindexermodel') || function_exists('post_indexer_make_current');
}

/**
 * Get Post Indexer table name.
 * @return string Table name.
 */
function eab_pi_get_table(): string {
    return class_exists('postindexermodel') ? 'network_posts' : 'site_posts';
}

/**
 * Get Post Indexer post date field.
 * @return string Date field.
 */
function eab_pi_get_post_date(): string {
    return class_exists('postindexermodel') ? 'post_date' : 'post_published_stamp';
}

/**
 * Get Post Indexer blog ID field.
 * @return string Blog ID field.
 */
function eab_pi_get_blog_id(): string {
    return class_exists('postindexermodel') ? 'BLOG_ID' : 'blog_id';
}

/**
 * Get Post Indexer post ID field.
 * @return string Post ID field.
 */
function eab_pi_get_post_id(): string {
    return class_exists('postindexermodel') ? 'ID' : 'post_id';
}
