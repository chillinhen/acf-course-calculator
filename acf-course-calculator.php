<?php
/*
Plugin Name: ACF Course Calculator
Description: Plugin zur Berechnung der Kurskosten und Rabatte, basierend auf ACF-Daten
Version: 1.2
*/

// Enqueue Styles und Scripts
function acf_course_calculator_enqueue() {
    wp_enqueue_style('calculator-styles', plugin_dir_url(__FILE__) . 'assets/calculator-styles.css');
    wp_enqueue_script('calculator-script', plugin_dir_url(__FILE__) . 'assets/calculator-script.js', [], null, true);

    // ACF-Daten abrufen
    $modulesCourses = [];
    if (have_rows('course-price-options', 'option')) {
        while (have_rows('course-price-options', 'option')) : the_row();
            $modulesCourses[] = [
                'name'  => get_sub_field('name', 'option'),
                'value' => get_sub_field('preis', 'option'),
            ];
        endwhile;
    }

    $listDiscount = [];
    if (have_rows('rabatte', 'option')) {
        while (have_rows('rabatte', 'option')) : the_row();
            $listDiscount[] = [
                'nr'       => get_sub_field('modul', 'option'),
                'discount' => get_sub_field('rabatt-einzeln', 'option'),
            ];
        endwhile;
    }

    // Daten an JavaScript übergeben
    wp_localize_script('calculator-script', 'acfCourseData', [
        'moduleDataCourses' => $modulesCourses,
        'listDiscount' => $listDiscount,
    ]);
}
add_action('wp_enqueue_scripts', 'acf_course_calculator_enqueue');

// Shortcode generieren
function acf_course_calculator_shortcode() {
    if (!function_exists('get_field')) {
        return '<p>Bitte installieren und aktivieren Sie das ACF-Plugin.</p>';
    }

    $headline = esc_html(get_field('calculator_headline', 'option'));
    $labelModuleStart = esc_html(get_field('label-module-start', 'option'));
    $labelModuleGoal = esc_html(get_field('label-module-goal', 'option'));
    $labelRegPrice = esc_html(get_field('label-reg-price', 'option'));
    $labelCourseCount = esc_html(get_field('label-course-count', 'option'));
    $labelDiscount = esc_html(get_field('label-dicount', 'option'));
    $labelResult = esc_html(get_field('label-result', 'option'));
    $labelDiscountResult = esc_html(get_field('label-discount-result', 'option'));

    ob_start(); ?>
    <form id="courseCalculator">
        <?php if ($headline): ?>
            <legend><?php echo $headline; ?></legend>
        <?php endif; ?>
        <div class="form-group">
            <label for="moduleStart"><strong><?php echo $labelModuleStart; ?></strong></label>
            <select id="moduleStart" class="form-control"></select>
        </div>
        <div class="form-group">
            <label for="moduleGoal"><strong><?php echo $labelModuleGoal; ?></strong></label>
            <select id="moduleGoal" class="form-control"></select>
        </div>
        <hr>
        <div class="form-group" id="rowCount">
            <div class="column"><strong><?php echo $labelCourseCount; ?></strong></div>
            <div class="column">
                <!-- <span class="form-control" id="countCourses"><span> -->
                <input class="form-control" id="countCourses" type="text" value="" readonly />
            </div>
        </div>
        <div class="form-group" id="rowPriceReg">
            <div class="column"><label for="showPriceReg"><?php echo $labelRegPrice; ?></label></div>
            <div class="column"><div class="price d-flex align-items-baseline justify-content-end"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div></div>
        </div>
        <div class="form-group" id="rowDiscount" aria-hidden="true">
            <div class="column"><label for="showDiscount"><?php echo $labelDiscount; ?></label></div>
            <div class="column">
                <div class="price d-flex align-items-baseline justify-content-end">
                    <input class="form-control" id="showDiscount" type="text" value="" readonly />
                </div>
            </div>
        </div>
        <hr>
        <div class="form-group d-flex" id="rowPriceAll">
            <div class="column">
                <label for="showPriceRegAll">
                    <strong id="labelResult"><?php echo $labelResult; ?></strong>
                    <strong id="labelDiscountResult" class="d-none"><?php echo $labelDiscountResult; ?></strong>
                </label>
            </div>
            <div class="column"><div class="price d-flex align-items-baseline justify-content-end"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div></div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('acf_course_calculator', 'acf_course_calculator_shortcode');?>