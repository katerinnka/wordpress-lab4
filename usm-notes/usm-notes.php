<?php
/*
Plugin Name: USM Notes
Description: Plugin for notes with priority and reminder date
Version: 1.0
Author: Ecaterina Cazac
*/

if (!defined('ABSPATH')) {
    exit;
}

function usm_register_notes_cpt() {
    $labels = array(
        'name'               => 'Заметки',
        'singular_name'      => 'Заметка',
        'menu_name'          => 'Заметки',
        'name_admin_bar'     => 'Заметка',
        'add_new'            => 'Добавить новую',
        'add_new_item'       => 'Добавить заметку',
        'new_item'           => 'Новая заметка',
        'edit_item'          => 'Редактировать заметку',
        'view_item'          => 'Просмотреть заметку',
        'all_items'          => 'Все заметки',
        'search_items'       => 'Искать заметки',
        'not_found'          => 'Заметки не найдены',
        'not_found_in_trash' => 'В корзине заметок нет',
    );

    $args = array(
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-edit-page',
        'supports'     => array('title', 'editor', 'author', 'thumbnail'),
        'show_in_rest' => true,
    );

    register_post_type('usm_note', $args);
}

add_action('init', 'usm_register_notes_cpt');

function usm_register_priority_taxonomy() {
    $labels = array(
        'name'              => 'Приоритеты',
        'singular_name'     => 'Приоритет',
        'search_items'      => 'Искать приоритет',
        'all_items'         => 'Все приоритеты',
        'parent_item'       => 'Родительский приоритет',
        'parent_item_colon' => 'Родительский приоритет:',
        'edit_item'         => 'Редактировать приоритет',
        'update_item'       => 'Обновить приоритет',
        'add_new_item'      => 'Добавить новый приоритет',
        'new_item_name'     => 'Название нового приоритета',
        'menu_name'         => 'Приоритет',
    );

    $args = array(
        'hierarchical' => true,
        'public'       => true,
        'labels'       => $labels,
        'show_in_rest' => true,
    );

    register_taxonomy('usm_priority', array('usm_note'), $args);
}

add_action('init', 'usm_register_priority_taxonomy');
/**
 * Add meta box
 */
function usm_add_due_date_metabox() {
    add_meta_box(
        'usm_due_date_box',
        'Due Date',
        'usm_render_due_date_metabox',
        'usm_note',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'usm_add_due_date_metabox');

/**
 * Render meta box
 */
function usm_render_due_date_metabox($post) {
    wp_nonce_field('usm_save_due_date', 'usm_due_date_nonce');

    $value = get_post_meta($post->ID, '_usm_due_date', true);

    echo '<label for="usm_due_date_field">Дата напоминания:</label><br>';
    echo '<input type="date" id="usm_due_date_field" name="usm_due_date_field" value="' . esc_attr($value) . '" required />';
}

/**
 * Save due date
 */
function usm_save_due_date_meta($post_id) {
    if (!isset($_POST['usm_due_date_nonce']) || !wp_verify_nonce($_POST['usm_due_date_nonce'], 'usm_save_due_date')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'usm_note') {
        return;
    }

    if (!isset($_POST['usm_due_date_field']) || empty($_POST['usm_due_date_field'])) {
        return;
    }

    $date = sanitize_text_field($_POST['usm_due_date_field']);
    update_post_meta($post_id, '_usm_due_date', $date);
}
add_action('save_post', 'usm_save_due_date_meta');