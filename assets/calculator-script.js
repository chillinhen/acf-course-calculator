document.addEventListener("DOMContentLoaded", () => {
    // HTML-Elemente
    const formElements = {
        moduleStart: document.getElementById("moduleStart"),
        moduleGoal: document.getElementById("moduleGoal"),
        countCourses: document.getElementById("countCourses"),
        showPriceReg: document.getElementById("showPriceReg"),
        showDiscount: document.getElementById("showDiscount"),
        showPriceAll: document.getElementById("showPriceAll"),
        rowDiscount: document.getElementById("rowDiscount"),
        labelResult: document.getElementById("labelResult"),
        labelDiscountResult: document.getElementById("labelDiscountResult"),
    };

    // Beispiel-Daten
    const moduleDataCourses = [
        { name: "keine Vorkenntnisse", value: 0 },
        { name: "A 1.1", value: 520 },
        { name: "A 1.2", value: 520 },
        { name: "A 2.1", value: 520 },
        { name: "A 2.2", value: 520 },
        { name: "B 1.1", value: 580 },
        { name: "B 1.2", value: 580 },
        { name: "B 2.1", value: 580 },
        { name: "B 2.2", value: 580 },
        { name: "C 1.1", value: 580 },
        { name: "C 1.2", value: 580 },
    ];

    const listDiscount = [0, 0, 50, 100, 150, 200]; // Beispiel: Rabattwerte basierend auf Kursanzahl

    // Hilfsfunktionen
    const createOptionsFromObj = (arr, sel, startIndex = 0) => {
        sel.innerHTML = '<option value="" disabled selected>Bitte wählen</option>';
        arr.slice(startIndex).forEach(module => {
            const option = document.createElement("option");
            option.value = module.value;
            option.textContent = module.name;
            sel.appendChild(option);
        });
    };

    const formatPrice = (price) => {
        return price.toLocaleString(navigator.language, { style: "currency", currency: navigator.language === "de" ? "EUR" : "USD" });
    };

    // Logik für die Berechnung
    const updateGoalOptions = () => {
        const startIndex = formElements.moduleStart.selectedIndex - 1; // -1 wegen "Bitte wählen"
        if (startIndex >= 0) {
            createOptionsFromObj(moduleDataCourses, formElements.moduleGoal, startIndex);
        }
    };

    const calculatePrice = () => {
        const startIndex = formElements.moduleStart.selectedIndex - 1; // -1 wegen "Bitte wählen"
        const goalIndex = formElements.moduleGoal.selectedIndex - 1;  // -1 wegen "Bitte wählen"

        if (startIndex < 0 || goalIndex < 0 || goalIndex < startIndex) {
            formElements.countCourses.value = "";
            formElements.showPriceReg.value = "";
            formElements.showDiscount.value = "";
            formElements.showPriceAll.value = "";
            formElements.rowDiscount.setAttribute("aria-hidden", "true");
            formElements.labelResult.classList.remove("d-none");
            formElements.labelDiscountResult.classList.add("d-none");
            return;
        }

        // Anzahl der Kurse
        const courseCount = goalIndex - startIndex + 1;
        formElements.countCourses.value = courseCount;

        // Gesamtpreis
        const totalPrice = moduleDataCourses.slice(startIndex, goalIndex + 1).reduce((sum, module) => sum + module.value, 0);
        formElements.showPriceReg.value = formatPrice(totalPrice);

        // Rabatt
        const discount = listDiscount[courseCount] || 0;
        if (discount > 0) {
            formElements.showDiscount.value = formatPrice(discount);
            formElements.rowDiscount.setAttribute("aria-hidden", "false");
            formElements.labelResult.classList.add("d-none");
            formElements.labelDiscountResult.classList.remove("d-none");
        } else {
            formElements.showDiscount.value = "";
            formElements.rowDiscount.setAttribute("aria-hidden", "true");
            formElements.labelResult.classList.remove("d-none");
            formElements.labelDiscountResult.classList.add("d-none");
        }

        // Gesamtpreis mit Rabatt
        const finalPrice = totalPrice - discount;
        formElements.showPriceAll.value = formatPrice(finalPrice);
    };

    // Event-Listener
    formElements.moduleStart.addEventListener("change", () => {
        updateGoalOptions();
        calculatePrice();
    });

    formElements.moduleGoal.addEventListener("change", calculatePrice);

    // Initialisierung
    createOptionsFromObj(moduleDataCourses, formElements.moduleStart);
});
