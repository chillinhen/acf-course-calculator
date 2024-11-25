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
    $labelCourseCount = esc_html(get_field('label-course-count','option'));
    $labelDiscount = esc_html(get_field('label-dicount','option'));
    $labelResult = esc_html(get_field('label-result','option'));
    $labelDiscountResult = esc_html(get_field('label-discount-result','option'));
   

    ob_start(); ?>
    <form id="courseCalculator">
        <?php if($headline) : ?>
            <legend><?php echo esc_html($headline);?></legend>
        <?php endif; ?>
        <div class="form-group my-3">
            <label for="moduleStart"><strong><?php echo (esc_html($labelModuleStart)) ? esc_html($labelModuleStart) : '';?></strong></label>
            <select id="moduleStart" class="form-control"></select>
        </div>
        <div class="form-group my-3">
            <label for="moduleGoal"><strong><?php echo (esc_html($labelModuleGoal)) ? esc_html($labelModuleGoal) : '';?></strong></label>
            <select id="moduleGoal" class="form-control"></select>
        </div>
        <hr>
        <div class="form-group d-flex my-3">
        <div class="col-md-6">
            <label for="countCourses"><strong><?php echo (esc_html($labelCourseCount)) ? esc_html($labelCourseCount) : '';?></strong></label>
        </div>
        <div class="col-md-6">
            <input class="form-control" id="countCourses" type="text" value="" readonly />
        </div>
        </div>              
        <div class="form-group d-flex my-3">
            <div class="col-md-6"><label for="showPriceReg"><?php echo (esc_html($labelRegPrice)) ? esc_html($labelRegPrice) : '';?></label></div>
            <div class="col-md-6"><input class="form-control" id="showPriceReg" type="text" value="" readonly /></div>
        </div>
        <div class="form-group d-flex my-3" id="rowDiscount">
            <div class="col-md-6"><label for="showDiscount"><?php echo (esc_html($labelDiscount)) ? esc_html($labelDiscount) : '';?></label></div>
            <div class="col-md-6"><input class="form-control" id="showDiscount" type="text" value="" readonly /></div>
        </div>
        <hr>
        <div class="form-group d-flex my-3 priceall">
            <div class="col-md-6">
                <label for="showPriceRegAll">
                    <strong id="labelResult"><?php echo (esc_html($labelResult)) ? esc_html($labelResult) : '';?></strong>
                    <strong id="labelDiscountResult" class="d-none"><?php echo (esc_html($labelDiscountResult)) ? esc_html($labelDiscountResult) : '';?></strong>
                </label>
            </div>
            <div class="col-md-6"><input class="form-control" id="showPriceAll" type="text" value="" readonly /></div>
        </div>
    </form>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log('test');
        //setup vars
        const moduleStart = document.getElementById("moduleStart");
        const moduleGoal = document.getElementById("moduleGoal");
        const countCourses = document.getElementById("countCourses");
        const showPriceReg = document.getElementById("showPriceReg");
        const showDiscount = document.getElementById("showDiscount");
        const showPriceAll = document.getElementById("showPriceAll");

        const rowDiscount = document.getElementById("rowDiscount");
        const labelPriceAll = document.querySelector("label[for='showPriceAll']"); 
        const labelResult = document.getElementById("labelResult");
        const labelDiscountResult = document.getElementById("labelDiscountResult");
        
        if (!rowDiscount) {
            console.warn("Element #rowDiscount wurde nicht gefunden. Rabatt wird möglicherweise nicht korrekt angezeigt.");
        }
        if (!labelPriceAll) {
            console.warn("Element label[for='showPriceAll'] wurde nicht gefunden. Gesamtpreis-Label wird möglicherweise nicht korrekt angezeigt.");
        }

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
            console.log("Berechneter Rabatt:", discount);
            return discount;
        };

        // Berechnung der Preise und Rabatte
        const calculatePrices = () => {
            const startIndex = moduleStart.selectedIndex;
            const goalIndex = moduleGoal.selectedIndex;

            console.log("Startindex:", startIndex, "Zielindex:", goalIndex);

            if (startIndex !== -1 && goalIndex !== -1) {
                const pricesInBetween = acfCourseData.moduleDataCourses
                    .slice(startIndex, startIndex + goalIndex + 1)
                    .map(module => parseFloat(module.value));

                const courseCount = pricesInBetween.length;
                const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);
                const discount = calculateDiscount(courseCount);
                const finalPrice = totalPrice - discount;

                console.log("Preise zwischen Auswahl:", pricesInBetween);
                console.log("Gesamtsumme ohne Rabatt:", totalPrice);
                console.log("Anzahl der gebuchten Kurse:", courseCount);
                console.log("Rabatt:", discount);
                console.log("Gesamtpreis:", finalPrice);
                
                countCourses.value = courseCount;
                showPriceReg.value = totalPrice.toFixed(2);
                showPriceAll.value = finalPrice.toFixed(2);

                // Rabatt anzeigen oder verstecken
                if (discount > 0) {
                    rowDiscount.style.display = "flex";
                    showDiscount.value = discount.toFixed(2);
                    labelResult.classList.add('d-none');
                    labelDiscountResult.classList.remove('d-none');
                    //labelPriceAll.textContent = "Ihr rabattierter Gesamtpreis:";
                } else {
                    rowDiscount.style.display = "none";
                    labelResult.classList.remove('d-none');
                    labelDiscountResult.classList.add('d-none');
                    //labelPriceAll.textContent = "Gesamtpreis:";
                }
            } else {
                console.warn("Ungültige Auswahl für Start- oder Zielmodul.");
                showPriceReg.value = "";
                showDiscount.value = "";
                showPriceAll.value = "";
            }
    };



        // Initial Dropdowns mit Optionen befüllen
        createOptionsFromObj(acfCourseData.moduleDataCourses, moduleStart);
        createOptionsFromObj(acfCourseData.moduleDataCourses, moduleGoal);

        moduleStart.selectedIndex = 0;
        moduleGoal.selectedIndex = 0;

        calculatePrices();

        moduleStart.addEventListener("change", function () {
            const startIndex = moduleStart.selectedIndex;

            if (startIndex !== -1) {
                const filteredOptions = acfCourseData.moduleDataCourses.slice(startIndex + 1);
                createOptionsFromObj(filteredOptions, moduleGoal);
                moduleGoal.selectedIndex = 0;
                calculatePrices();
            }
        });
        moduleGoal.addEventListener("change", calculatePrices);

    });

    </script>
    <?php 
    return ob_get_clean();
}

add_shortcode('acf_course_calculator', 'acf_course_calculator');
?>
