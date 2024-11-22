<?php
/*
Plugin Name: ACF Course Calculator
Description: Individuelles Plugin zur Berechnung der Kurskosten und Rabatte, einzubinden auf Kurseiten
Version: 1.0
*/

function acf_course_calculator() {
    if (!function_exists('get_field')) {
        return '<p>Bitte installiere und aktiviere das ACF-Plugin.</p>';
    }

    wp_enqueue_style('calculator-styles', plugin_dir_url(__FILE__) . 'assets/calculator-styles.css');
    wp_enqueue_script('calculator-script', plugin_dir_url(__FILE__) . 'assets/calculator-script.js', array(), null, true);

    $modulesCourses = array();
    $listDiscount = array();

    if (have_rows('course-price-options', 'option')) {
        while (have_rows('course-price-options', 'option')) : the_row();
            $modulesCourses[] = array(
                "name"  => get_sub_field('name', 'option'),
                "value" => get_sub_field('preis', 'option'),
            );
        endwhile;
    }

    if (have_rows('rabatte', 'option')) {
        while (have_rows('rabatte', 'option')) : the_row();
            $listDiscount[] = array(
                "nr"       => get_sub_field('modul', 'option'),
                "discount" => get_sub_field('rabatt-einzeln', 'option'),
            );
        endwhile;
    }

    wp_localize_script('calculator-script', 'acfCourseData', array(
        'moduleDataCourses' => $modulesCourses,
        'discountData'    => $listDiscount,
    ));

    // labels
    // Headline
    $headline = get_field('calculator_headline','option');
    $labelModuleStart = get_field('label-module-start','option');
    $labelModuleGoal = get_field('label-module-goal','option');
    $labelRegPrice = esc_html(get_field('label-reg-price','option'));
    $labelDiscount = esc_html(get_field('label-dicount','option'));
    $labelResult = esc_html(get_field('label-result','option'));
    $labelDiscountResult = esc_html(get_field('label-discount-result','option'));
   

    ob_start(); ?>
    <form id="courseCalculator">
        <?php if($headline) : ?>
            <legend><?php echo esc_html($headline);?></legend>
        <?php endif; ?>
        <div class="form-group my-3">
            <label for="moduleStart"><strong><?php echo (esc_html($labelModuleStart)) ? esc_html($labelModuleStart) : '';?></strong></label></label>
            <select id="moduleStart" class="form-control"></select>
        </div>
        <div class="form-group my-3">
            <label for="moduleGoal"><strong><?php echo (esc_html($labelModuleGoal)) ? esc_html($labelModuleGoal) : '';?></strong></label></label>
            <select id="moduleGoal" class="form-control"></select>
        </div>
        <hr>                
        <div class="form-group row my-3">
            <div class="col-md-6"><label for="showPriceReg"><?php echo (esc_html($labelRegPrice)) ? esc_html($labelRegPrice) : '';?></label></div>
            <div class="col-md-6"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div>
        </div>
        <div class="form-group row my-3" id="rowDiscount">
            <div class="col-md-6"><label for="showDiscount"><?php echo (esc_html($labelDiscount)) ? esc_html($labelDiscount) : '';?></label></div>
            <div class="col-md-6"><input class="form-control" id="showDiscount" type="text" value="" readonly /></div>
        </div>
        <hr>
        <div class="form-group row my-3 priceall">
            <div class="col-md-6"><label for="showPriceRegAll"><strong><?php echo (esc_html($labelResult)) ? esc_html($labelResult) : '';?></strong></label></div>
            <div class="col-md-6"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div>
        </div>
    </form>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
    const moduleStart = document.getElementById("moduleStart");
    const moduleGoal = document.getElementById("moduleGoal");
    const showPriceReg = document.getElementById("showPriceReg"); // Eingabefeld für den Preis
    const showDiscount = document.getElementById("showDiscount");
    const showPriceAll = document.getElementById("showPriceAll");

    const createOptionsFromObj = (arr, sel) => {
        sel.innerHTML = '';
        if (arr) {
            arr.forEach(module => {
                const option = document.createElement("option");
                option.value = module.value; // Preis als Wert speichern
                option.textContent = module.name;
                sel.appendChild(option);
            });
        }
    };

    // Rabatt berechnen
    const calculateDiscount = (courseCount) => {
        let discount = 0;
        acfCourseData.discountData.forEach(d => {
            if (courseCount >= d.nr) {
                discount = Math.max(discount, parseFloat(d.discount));
            }
        });
        return discount;
    };

    // Berechnung der Preise und Rabatte
    const calculatePrices = () => {
        const startIndex = moduleStart.selectedIndex;
        const goalIndex = moduleGoal.selectedIndex;

        if (startIndex !== -1 && goalIndex !== -1) {
            // Ermitteln der Preise zwischen den Indizes (inklusive `moduleStart`, exklusiv `moduleGoal`)
            const pricesInBetween = acfCourseData.moduleDataCourses
                .slice(startIndex, startIndex + goalIndex + 1) // Zielbereich
                .map(module => parseFloat(module.value)); // Preise extrahieren und zu Zahlen konvertieren

            // Anzahl der gebuchten Kurse
            const courseCount = pricesInBetween.length;

            // Summe berechnen
            const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);

            // Rabatt basierend auf der Anzahl der Kurse
            const discount = calculateDiscount(courseCount);

            // Gesamtpreis berechnen
            const finalPrice = totalPrice - discount;

            // Konsolenausgabe für Debugging
            console.log("Preise zwischen Auswahl:", pricesInBetween);
            console.log("Gesamtsumme ohne Rabatt:", totalPrice);
            console.log("Anzahl der gebuchten Kurse:", courseCount);
            console.log("Rabatt:", discount);
            console.log("Gesamtpreis:", finalPrice);

            // Werte in die Felder eintragen
            showPriceReg.value = totalPrice.toFixed(2); // Fixiere auf 2 Nachkommastellen
            showDiscount.value = discount.toFixed(2); // Rabatt anzeigen
            showPriceAll.value = finalPrice.toFixed(2); // Gesamtpreis anzeigen
        } else {
            // Falls keine gültige Auswahl getroffen wurde, die Felder leeren
            showPriceReg.value = "";
            showDiscount.value = "";
            showPriceAll.value = "";
        }
    };

        // Initial Dropdowns mit Optionen befüllen
        createOptionsFromObj(acfCourseData.moduleDataCourses, moduleStart);
        createOptionsFromObj(acfCourseData.moduleDataCourses, moduleGoal);

        // Initiale Auswahl setzen
        moduleStart.selectedIndex = 0; // Erster Index
        //moduleGoal.selectedIndex = Math.min(1, moduleGoal.options.length - 1); // Zweiter Index, falls vorhanden
        moduleGoal.selectedIndex = 0;
        // Initiale Berechnung ausführen
        calculatePrices();

        // Aktualisieren von `moduleGoal`, basierend auf der Auswahl in `moduleStart`
        moduleStart.addEventListener("change", function () {
            const startIndex = moduleStart.selectedIndex;

            if (startIndex !== -1) {
                // Filtere die Optionen ab dem nächsten Index
                const filteredOptions = acfCourseData.moduleDataCourses.slice(startIndex + 1);

                // Aktualisiere die Liste für `moduleGoal`
                createOptionsFromObj(filteredOptions, moduleGoal);

                // Standardwert für `moduleGoal` setzen
                moduleGoal.selectedIndex = 0;

                // Berechnung nach Anpassung
                calculatePrices();
            }
        });

    // Event-Listener für die Berechnung bei Änderung von `moduleGoal`
    moduleGoal.addEventListener("change", calculatePrices);
});

    </script>
    <?php 
    return ob_get_clean();
}

add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>
