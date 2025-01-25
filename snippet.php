<?php
/**
 * Mehrsprachiger ACF-basierter Preisrechner
 */

// Sicherheitsabfrage, um direkten Zugriff zu verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Assets einbinden
function enqueue_calculator_assets() {
    wp_enqueue_style('calculator-styles', plugin_dir_url(__FILE__) . 'assets/calculator-styles.css');
    wp_enqueue_script('calculator-script', plugin_dir_url(__FILE__) . 'assets/calculator-script.js', ['jquery'], null, true);

    $current_language = pll_current_language();

    // Labels laden
    $labels = get_field('labels', 'option');
    if (!$labels) {
        error_log("ACF labels field is empty or not found.");
        $labels = [];
    }
    $filtered_labels = array_filter($labels, function($label) use ($current_language) {
        return $label['language'] === $current_language;
    });
    $current_labels = reset($filtered_labels);

    // Module laden
    $modules = get_field('modules', 'option');
    if (!$modules) {
        error_log("ACF modules field is empty or not found.");
        $modules = [];
    } else {
        error_log("Loaded modules: " . print_r($modules, true));
    }
    $filtered_modules = array_filter($modules, function($module) use ($current_language) {
        return $module['language'] === $current_language;
    });

    // Subfields extrahieren
    $processed_modules = array_map(function($module) {
        return [
            'name' => $module['module_name'] ?? '',
            'price' => $module['module_price'] ?? 0
        ];
    }, $filtered_modules);

    error_log("Processed modules: " . print_r($processed_modules, true));

    // Rabatte laden (global, nicht mehrsprachig)
    $discounts = get_field('discounts', 'option');
    if (!$discounts) {
        error_log("ACF discounts field is empty or not found.");
        $discounts = [];
    }

    // Sprachabhängige Texte
    $localized_texts = [
        'placeholder' => $current_labels['placeholder'] ?? 'Bitte wählen',
        'currency' => '€',
    ];

    wp_localize_script('calculator-script', 'acfCourseData', [
        'moduleDataCourses' => array_values($processed_modules),
        'listDiscount' => $discounts, // Rabatte ohne Sprachfilter
        'texts' => $localized_texts,
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_calculator_assets');

// Formular rendern
function render_course_calculator() {
    $current_language = pll_current_language();

    // Labels laden
    $labels = get_field('labels', 'option');
    if (!$labels) {
        error_log("ACF labels field is empty or not found.");
        $labels = [];
    }
    $filtered_labels = array_filter($labels, function($label) use ($current_language) {
        return $label['language'] === $current_language;
    });
    $current_labels = reset($filtered_labels);

    // Labels extrahieren
    $label_start = $current_labels['label_module_start'] ?? 'Startmodul';
    $label_goal = $current_labels['label_module_goal'] ?? 'Zielmodul';
    $label_result = $current_labels['label_result'] ?? 'Gesamtkosten';
    $label_discount = $current_labels['label_discount'] ?? 'Rabatt';
    $label_final_price = $current_labels['label_final_price'] ?? 'Endpreis';

    ob_start();
    ?>
    <form id="courseCalculator">
        <div class="form-group">
            <label for="moduleStart"><?php echo esc_html($label_start); ?></label>
            <select id="moduleStart" name="moduleStart"></select>
        </div>
        <div class="form-group">
            <label for="moduleGoal"><?php echo esc_html($label_goal); ?></label>
            <select id="moduleGoal" name="moduleGoal"></select>
        </div>
        <div id="rowCount" class="row">
            <label><?php echo esc_html($label_result); ?>:</label>
            <input type="text" id="countCourses" readonly />
        </div>
        <div id="rowPriceReg" class="row">
            <label><?php echo esc_html($label_result); ?>:</label>
            <input type="text" id="showPriceReg" readonly />
        </div>
        <div id="rowDiscount" class="row">
            <label><?php echo esc_html($label_discount); ?>:</label>
            <input type="text" id="showDiscount" readonly />
        </div>
        <div id="rowPriceAll" class="row">
            <label><?php echo esc_html($label_final_price); ?>:</label>
            <input type="text" id="showPriceAll" readonly />
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Prüfen, ob Module korrekt geladen wurden
            console.log('Module Data Courses:', acfCourseData.moduleDataCourses);

            // ModuleStart-Dropdown füllen
            const moduleStart = document.getElementById('moduleStart');
            acfCourseData.moduleDataCourses.forEach((module, index) => {
                const option = document.createElement('option');
                option.value = module.name;
                option.textContent = `${module.name} (${module.price}€)`;
                option.dataset.index = index;
                moduleStart.appendChild(option);
            });

            // Debug: Prüfen, ob Dropdown korrekt befüllt wurde
            console.log('Dropdown Inhalt:', moduleStart);
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('course_calculator', 'render_course_calculator');
