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
            totalPrice += parseFloat(modules[i].value);
            console.log(totalPrice);
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

        // Rabattberechnung mit korrekter Formatierung
        //const discount = discounts.find(d => d.nr == totalCourses)?.discount || 0;
        const discountEntry = discounts.find(d => d.nr == totalCourses);
        const discount = parseFloat(discountEntry?.discount || 0);
        
        if(discount > 0) {
            formElements.showDiscount.value = discount.toLocaleString(undefined, { style: "currency", currency: "EUR" });
        } else {formElements.showDiscount.value = '';}
        // formElements.showDiscount.value = discount > 0 
        //     ? discount.toLocaleString(undefined, { style: "currency", currency: "EUR" }) 
        //     : ''; // Hier wird die Währungsformatierung korrekt angewendet

        if (discount > 0) {
            formElements.rowDiscount.setAttribute("aria-hidden", "false");
            formElements.rowDiscount.classList.add('d-flex');
        } else {
            formElements.rowDiscount.setAttribute("aria-hidden", "true");
            formElements.rowDiscount.classList.remove('d-flex');
        }

        const finalPrice = totalPrice - discount;
        formElements.showPriceAll.value = finalPrice.toLocaleString(undefined, { style: "currency", currency: "EUR" });

        // Labels aktualisieren basierend auf Rabatt
        if (discount > 0) {
            formElements.labelResult.classList.add('d-none');
            formElements.labelDiscountResult.classList.remove('d-none');
        } else {
            formElements.labelResult.classList.remove('d-none');
            formElements.labelDiscountResult.classList.add('d-none');
        }
    };