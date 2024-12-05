//Chat
const calculatePrices = () => {
    const startIndex = moduleStart.selectedIndex;
    const goalIndex = moduleGoal.selectedIndex;

    if (startIndex !== -1 && goalIndex !== -1) {
        const pricesInBetween = acfCourseData.moduleDataCourses
            .slice(startIndex, startIndex + goalIndex + 1)
            .map(module => parseFloat(module.value));

        const courseCount = pricesInBetween.length;
        const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);
        const discount = calculateDiscount(courseCount);
        const finalPrice = totalPrice - discount;

        // Ausgabe mit deutscher Formatierung
        countCourses.value = courseCount;
        showPriceReg.value = totalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        showPriceAll.value = finalPrice.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (discount > 0) {
            rowDiscount.style.display = "flex";
            showDiscount.value = discount.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            labelResult.classList.add('d-none');
            labelDiscountResult.classList.remove('d-none');
        } else {
            rowDiscount.style.display = "none";
            labelResult.classList.remove('d-none');
            labelDiscountResult.classList.add('d-none');
        }
    } else {
        console.warn("Ungültige Auswahl für Start- oder Zielmodul.");
        showPriceReg.value = "";
        showDiscount.value = "";
        showPriceAll.value = "";
    }
};
