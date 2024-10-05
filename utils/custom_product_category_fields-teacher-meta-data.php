<?php

/**
 * custom_product_category_fields (Teacher Meta Data)
 */
// Add custom fields to product category creation page
function custom_category_fields()
{
?>
    <div class="form-field">
        <label for="portal">Domain/Portal</label>
        <input type="text" name="portal" id="portal" />
        <p class="description">Enter the value for Domain/Portal.</p>
    </div>
    <!-- <div class="form-field">
        <label for="custom_image">Image</label>
        <input type="text" name="custom_image" id="custom_image" />
        <p class="description">Enter the value for Image.</p>
    </div>

    <div class="form-field">
        <label for="custom_logo">Logo</label>
        <input type="text" name="custom_logo" id="custom_logo" />
        <p class="description">Enter the value for Logo.</p>
    </div>

    <div class="form-field">
        <label for="custom_icon">Icon</label>
        <input type="text" name="custom_icon" id="custom_icon" />
        <p class="description">Enter the value for Icon.</p>
    </div>

    <div class="form-field">
        <label for="teacher_slug">Teacher Slug</label>
        <input type="text" name="teacher_slug" id="teacher_slug" />
        <p class="description">Enter the value for teacher slug that is shared between all his categories.</p>
    </div>

    <div class="form-field">
        <label for="teacher_identifier">Teacher Identifier</label>
        <input type="text" name="teacher_identifier" id="teacher_identifier" />
        <p class="description">Enter the value for teacher identifier that is used to identify the current website's teacher.</p>
    </div> -->
<?php
}
add_action('product_cat_add_form_fields', 'custom_category_fields');

// Add custom fields to product category edit page
function edit_custom_category_fields($term)
{
    $portal = get_term_meta($term->term_id, 'portal', true);
    $cat_order = get_term_meta($term->term_id, 'cat_order', true);
    // $custom_logo = get_term_meta($term->term_id, 'custom_logo', true);
    // $custom_icon = get_term_meta($term->term_id, 'custom_icon', true);
    // $teacher_slug = get_term_meta($term->term_id, 'teacher_slug', true);
    // $teacher_identifier = get_term_meta($term->term_id, 'teacher_identifier', true);

?>
    <tr class="form-field">
        <th scope="row"><label for="portal">Domain/Portal</label></th>
        <td>
            <input type="text" name="portal" id="portal" value="<?php echo esc_attr($portal); ?>" />
            <p class="description">Enter the value for Domain/Portal.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="cat_order">Order</label></th>
        <td>
            <input type="number" name="cat_order" id="cat_order" value="<?php echo esc_attr($cat_order); ?>" />
            <p class="description">Enter the category order.</p>
        </td>
    </tr>

    <!-- <tr class="form-field">
        <th scope="row"><label for="custom_logo">Logo</label></th>
        <td>
            <input type="text" name="custom_logo" id="custom_logo" value="<?php // echo esc_attr($custom_logo); ?>" />
            <p class="description">Enter the value for Logo.</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="custom_icon">Icon</label></th>
        <td>
            <input type="text" name="custom_icon" id="custom_icon" value="<?php // echo esc_attr($custom_icon); ?>" />
            <p class="description">Enter the value for Icon.</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="teacher_slug">Teacher Slug</label></th>
        <td>
            <input type="text" name="teacher_slug" id="teacher_slug" value="<?php // echo esc_attr($teacher_slug); ?>" />
            <p class="description">Enter the value for teacher slug that is shared between all his categories.</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="teacher_identifier">Teacher Identifier</label></th>
        <td>
            <input type="text" name="teacher_identifier" id="teacher_identifier" value="<?php // echo esc_attr($teacher_identifier); ?>" />
            <p class="description">Enter the value for teacher identifier that is used to identify the current website's teacher.</p>
        </td>
    </tr> -->
<?php
}
add_action('product_cat_edit_form_fields', 'edit_custom_category_fields');

// Save custom fields when a product category is created or edited
function save_custom_category_fields($term_id)
{
    if (isset($_POST['portal'])) {
        update_term_meta($term_id, 'portal', sanitize_text_field($_POST['portal']));
    }

    if (isset($_POST['cat_order'])) {
        update_term_meta($term_id, 'cat_order', sanitize_text_field($_POST['cat_order']));
    }

    // if (isset($_POST['custom_logo'])) {
    //     update_term_meta($term_id, 'custom_logo', sanitize_text_field($_POST['custom_logo']));
    // }

    // if (isset($_POST['custom_icon'])) {
    //     update_term_meta($term_id, 'custom_icon', sanitize_text_field($_POST['custom_icon']));
    // }

    // if (isset($_POST['teacher_slug'])) {
    //     update_term_meta($term_id, 'teacher_slug', sanitize_text_field($_POST['teacher_slug']));
    // }

    // if (isset($_POST['teacher_identifier'])) {
    //     update_term_meta($term_id, 'teacher_identifier', sanitize_text_field($_POST['teacher_identifier']));
    // }
}
add_action('created_product_cat', 'save_custom_category_fields');
add_action('edited_product_cat', 'save_custom_category_fields');
