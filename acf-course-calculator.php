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

    // Labels laden (sprachabhängig)
    $labels = get_field('labels', 'option');
    if (!$labels) {
        error_log("ACF labels field is empty or not found.");
        $labels = [];
    }
    $filtered_labels = array_filter($labels, function($label) use ($current_language) {
        return $label['language'] === $current_language;
    });
    $current_labels = reset($filtered_labels);

    // Knowledge Levels laden (sprachabhängig)
    $knowledges = get_field('knowledges', 'option');
    if (!$knowledges) {
        error_log("ACF knowledges field is empty or not found.");
        $knowledges = [];
    }
    $filtered_knowledges = array_filter($knowledges, function($knowledge) use ($current_language) {
        return $knowledge['language'] === $current_language;
    });
    $processed_knowledges = array_map(function($knowledge) {
        return [
            'name' => $knowledge['knowledge_name'] ?? '',
            'price' => $knowledge['knowledge_price'] ?? 0
        ];
    }, $filtered_knowledges);

    // Module laden (global, nicht sprachabhängig)
    $modules = get_field('modules', 'option');
    if (!$modules) {
        error_log("ACF modules field is empty or not found.");
        $modules = [];
    }
    $processed_modules = array_map(function($module) {
        return [
            'name' => $module['module_name'] ?? '',
            'price' => $module['module_price'] ?? 0
        ];
    }, $modules);

    // Rabatte laden (global, nicht mehrsprachig)
    $discounts = get_field('discounts', 'option');
    if (!$discounts) {
        error_log("ACF discounts field is empty or not found.");
        $discounts = [];
    }
    $processed_discounts = array_map(function($discount) {
        return [
            'course_count' => $discount['course_count'] ?? 0,
            'discount_value' => $discount['discount_value'] ?? 0
        ];
    }, $discounts);

    // Sprachabhängige Texte
    $localized_texts = [
        'headline' => $current_labels['headline'] ?? 'Preisrechner',
        'start_label' => $current_labels['label_start'] ?? 'Startmodul',
        'goal_label' => $current_labels['label_goal'] ?? 'Zielmodul',
        'count_label' => $current_labels['label_course_count'] ?? 'Anzahl Kurse',
        'price_label' => $current_labels['label_price'] ?? 'Preis',
        'discount_label' => $current_labels['label_discount'] ?? 'Rabatt',
        'final_price_label' => $current_labels['label_final_price'] ?? 'Gesamtpreis',
        'placeholder' => 'Bitte wählen',
        'currency' => '€',
    ];

    wp_localize_script('calculator-script', 'acfCourseData', [
        'knowledgeLevels' => array_values($processed_knowledges),
        'moduleDataCourses' => array_values($processed_modules),
        'listDiscount' => $processed_discounts, // Rabatte korrekt verarbeitet
        'texts' => $localized_texts,
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_calculator_assets');

// Formular rendern
function render_course_calculator() {
    ?>
    <form id="courseCalculator">
        <h1 id="calculatorHeadline"></h1>
        <div class="form-group">
            <label for="moduleStart" id="startLabel"></label>
            <select id="moduleStart" name="moduleStart"></select>
        </div>
        <div class="form-group">
            <label for="moduleGoal" id="goalLabel"></label>
            <select id="moduleGoal" name="moduleGoal"></select>
        </div>
        <div id="rowCount" class="row">
            <label id="countLabel"></label>
            <input type="text" id="countCourses" readonly />
        </div>
        <div id="rowPriceReg" class="row">
            <label id="priceLabel"></label>
            <input type="text" id="showPriceReg" readonly />
        </div>
        <div id="rowDiscount" class="row">
            <label id="discountLabel"></label>
            <input type="text" id="showDiscount" readonly />
        </div>
        <div id="rowPriceAll" class="row">
            <label id="finalPriceLabel"></label>
            <input type="text" id="showPriceAll" readonly />
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Labels dynamisch einsetzen
            const texts = acfCourseData.texts;
            document.getElementById('calculatorHeadline').textContent = texts.headline;
            document.getElementById('startLabel').textContent = texts.start_label;
            document.getElementById('goalLabel').textContent = texts.goal_label;
            document.getElementById('countLabel').textContent = texts.count_label;
            document.getElementById('priceLabel').textContent = texts.price_label;
            document.getElementById('discountLabel').textContent = texts.discount_label;
            document.getElementById('finalPriceLabel').textContent = texts.final_price_label;

            // Knowledge Levels in Dropdown einfügen
            const moduleStart = document.getElementById('moduleStart');
            acfCourseData.knowledgeLevels.forEach((knowledge, index) => {
                const option = document.createElement('option');
                option.value = knowledge.price;
                option.textContent = `${knowledge.name} (${knowledge.price}€)`;
                option.dataset.index = index;
                moduleStart.appendChild(option);
            });

            // Module in Dropdown einfügen
            const moduleGoal = document.getElementById('moduleGoal');
            acfCourseData.moduleDataCourses.forEach((module, index) => {
                const option = document.createElement('option');
                option.value = module.price;
                option.textContent = `${module.name} (${module.price}€)`;
                option.dataset.index = index;
                moduleGoal.appendChild(option);
            });
        });
    </script>
    <?php
}
add_shortcode('acf_course_calculator', 'render_course_calculator');
