const calculatePrice = () => {
    const startIndex = parseInt(formElements.moduleStart.selectedOptions[0]?.dataset.index || -1);
    const endIndex = parseInt(formElements.moduleGoal.selectedOptions[0]?.dataset.index || -1);

    if (startIndex < 0 || endIndex < 0 || startIndex >= endIndex) {
        resetResults();
        return;
    }

    let totalCourses = 0;
    let totalPrice = 0;

    for (let i = startIndex; i < endIndex; i++) {
        totalCourses++;
        totalPrice += parseFloat(modules[i].value); // Umwandlung in Zahl sicherstellen
    }

    if (totalCourses > 0 && totalPrice > 0) {
        formElements.rowCount.classList.add('d-flex');
        formElements.rowPriceReg.classList.add('d-flex');
    } else {
        formElements.rowCount.classList.remove('d-flex');
        formElements.rowPriceReg.classList.remove('d-flex');
    }

    formElements.countCourses.value = totalCourses;
    formElements.showPriceReg.value = totalPrice.toLocaleString(undefined, { style: "currency", currency: "EUR" });

    const discount = discounts.find(d => d.nr == totalCourses)?.discount || 0;
    formElements.showDiscount.value = discount > 0 ? discount.toLocaleString(undefined, { style: "currency", currency: "EUR" }) : '';
    formElements.rowDiscount.setAttribute("aria-hidden", discount > 0 ? "false" : "true");

    const finalPrice = totalPrice - discount;
    formElements.showPriceAll.value = finalPrice.toLocaleString(undefined, { style: "currency", currency: "EUR" });

    if (discount > 0) {
        formElements.rowDiscount.classList.add('d-flex');
        formElements.labelDiscountResult.classList.add('d-flex');
        formElements.labelDiscountResult.classList.remove('d-none');
        formElements.labelResult.classList.add('d-none');
    } else {
        formElements.rowDiscount.classList.remove('d-flex');
        formElements.labelResult.classList.add('d-flex');
        formElements.labelResult.classList.remove('d-none');
        formElements.labelDiscountResult.classList.add('d-none');
    }
};
